<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class MonitoringDashboardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withSession(['iems.authenticated' => true]);
    }

    #[TestWith(['overview', 'Environmental Overview'])]
    #[TestWith(['areas-of-interest', 'Areas of Interest'])]
    #[TestWith(['monitoring', 'Environmental Monitoring'])]
    #[TestWith(['historical-baseline', 'Historical Baseline'])]
    #[TestWith(['alerts', 'Alerts & Anomalies'])]
    #[TestWith(['reports', 'Reports & Insights'])]
    #[TestWith(['poc-evaluation', 'PoC Evaluation & Roadmap'])]
    #[TestWith(['administration', 'Administration'])]
    public function test_connected_screen_renders_with_demonstration_context(string $screen, string $title): void
    {
        $response = $this->get('/'.$screen);

        $response->assertOk()->assertSee($title)->assertSee('Demonstration Data')->assertSee('Coastal Industrial')
            ->assertSee('River Catchment')->assertDontSee('Southern Strait')->assertDontSee('value="strait"', false);
    }

    public function test_empty_period_is_distinct_from_no_anomalies(): void
    {
        $response = $this->get('/overview?period=empty');

        $response->assertOk()->assertSee('No observations available')->assertDontSee('No anomalies detected');
        $response->assertViewHas('observations', []);
    }

    public function test_catchment_has_observations_without_detected_anomalies(): void
    {
        $response = $this->get('/overview?aoi=catchment');

        $response->assertOk()->assertSee('No anomalies detected')->assertSee('1,854');
        $response->assertViewHas('openAnomalies', 0);
    }

    public function test_parameter_and_period_filters_restrict_exported_records(): void
    {
        $response = $this->get('/observations/export?aoi=catchment&parameter=water&period=week');

        $response->assertOk()->assertDownload('iems-demonstration-observations.csv');
        $this->assertStringContainsString('River Catchment AOI', $response->streamedContent());
        $this->assertStringContainsString('17 Oct 2026', $response->streamedContent());
        $this->assertStringContainsString('5.8', $response->streamedContent());
        $this->assertStringNotContainsString('12 Oct 2026', $response->streamedContent());
        $this->assertStringNotContainsString('Methane', $response->streamedContent());
    }

    public function test_severity_filter_selects_matching_anomaly_detail(): void
    {
        $response = $this->get('/alerts?severity=Medium');

        $response->assertOk()->assertSee('ANM-025')->assertDontSee('ANM-026');
        $response->assertViewHas('selectedAnomaly', fn (array $item): bool => $item['id'] === 'ANM-025');
    }

    public function test_review_saves_validated_status_and_note_in_session(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 20)->setTime(10, 30));

        $response = $this->post('/alerts/ANM-026/review', ['status' => 'Under Review', 'note' => 'Review neighbouring acquisitions before field validation.']);

        $response->assertRedirect();
        $response->assertSessionHas('iems.reviews.ANM-026.status', 'Under Review');
        $response->assertSessionHas('iems.reviews.ANM-026.note', 'Review neighbouring acquisitions before field validation.');
    }

    public function test_invalid_review_does_not_save_session_state(): void
    {
        $response = $this->post('/alerts/ANM-026/review', ['status' => 'Confirmed leak', 'note' => 'x']);

        $response->assertSessionHasErrors(['status', 'note']);
        $response->assertSessionMissing('iems.reviews.ANM-026');
    }

    public function test_review_note_is_escaped_in_rendered_history(): void
    {
        $response = $this->withSession(['iems.reviews.ANM-026' => ['status' => 'Reviewed', 'note' => '<script>alert(1)</script>', 'updated_at' => '20 Oct 2026, 10:30 MYT']])->get('/alerts');

        $response->assertOk()->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
        $response->assertViewHas('openAnomalies', 1);
    }

    public function test_unknown_anomaly_cannot_be_reviewed(): void
    {
        $response = $this->post('/alerts/unknown/review', ['status' => 'Closed', 'note' => 'Unknown observation.']);

        $response->assertNotFound();
    }

    public function test_demo_configuration_saves_in_session(): void
    {
        $settings = ['methane_threshold' => 10, 'air_threshold' => 25, 'water_threshold' => 35, 'frequency' => 'Weekly', 'baseline' => '2025 annual reference'];

        $response = $this->post('/administration/settings', $settings);

        $response->assertRedirectToRoute('iems.screen', ['screen' => 'administration']);
        $response->assertSessionHas('iems.settings', $settings);
    }

    public function test_invalid_threshold_and_frequency_are_rejected(): void
    {
        $response = $this->post('/administration/settings', ['methane_threshold' => -5, 'air_threshold' => 101, 'water_threshold' => 'invalid', 'frequency' => 'Live', 'baseline' => 'missing']);

        $response->assertSessionHasErrors(['methane_threshold', 'air_threshold', 'water_threshold', 'frequency', 'baseline']);
        $response->assertSessionMissing('iems.settings');
    }

    #[TestWith(['progress', 'Periodic Progress Report'])]
    #[TestWith(['baseline', 'Environmental Baseline Assessment'])]
    #[TestWith(['findings', 'Environmental Monitoring Findings'])]
    #[TestWith(['evaluation', 'Final PoC Evaluation'])]
    #[TestWith(['roadmap', 'IEMS Implementation Roadmap'])]
    public function test_report_preview_renders_selected_type_and_aoi(string $type, string $title): void
    {
        $response = $this->get('/reports/print?type='.$type.'&aoi=catchment');

        $response->assertOk()->assertSee($title)->assertSee('River Catchment AOI')->assertSee('Print / Save PDF')->assertSee('illustrative sample data');
    }

    public function test_invalid_filter_is_rejected(): void
    {
        $response = $this->getJson('/monitoring?parameter=unknown');

        $response->assertUnprocessable()->assertJsonValidationErrors('parameter');
    }

    public function test_unknown_screen_returns_404(): void
    {
        $this->get('/unknown')->assertNotFound();
    }

    public function test_annual_baseline_selection_changes_reference_and_comparison(): void
    {
        $response = $this->get('/historical-baseline?reference=annual');

        $response->assertOk()->assertSee('2025 annual median')->assertSee('1,838')->assertSee('+4.7%');
        $response->assertSee('name="reference"', false);
    }

    #[TestWith(['/overview'])]
    #[TestWith(['/observations/export'])]
    #[TestWith(['/reports/print'])]
    public function test_removed_aoi_cannot_be_selected_or_exported(string $path): void
    {
        $response = $this->getJson($path.'?aoi=strait');

        $response->assertUnprocessable()->assertJsonValidationErrors('aoi');
    }

    public function test_report_builder_and_print_link_keep_selected_scope(): void
    {
        $response = $this->get('/reports?type=baseline&aoi=catchment&period=week&parameter=water&reference=annual');

        $response->assertOk()->assertViewHas('reportType', 'baseline')->assertViewHas('reference', 'annual');
        $response->assertSee('Environmental Baseline Assessment')->assertSee('2025 annual median');
        $response->assertSee(route('iems.report', ['type' => 'baseline', 'aoi' => 'catchment', 'period' => 'week', 'parameter' => 'water', 'reference' => 'annual']));
    }

    public function test_print_report_limits_parameters_and_preserves_reference(): void
    {
        $response = $this->get('/reports/print?aoi=catchment&period=week&parameter=water&reference=annual');

        $response->assertOk()->assertSee('Water quality')->assertSee('5.8')->assertSee('2025 annual reference');
        $response->assertDontSee('1,854')->assertDontSee('2.24');
        $response->assertSee('No open anomalies in the available observations.');
    }

    public function test_closing_all_flags_clears_overview_priority_review(): void
    {
        $review = ['status' => 'Closed', 'note' => 'No persistent pattern in repeat acquisitions.', 'updated_at' => '20 Oct 2026, 10:30 MYT'];

        $response = $this->withSession(['iems.reviews.ANM-026' => $review, 'iems.reviews.ANM-025' => $review])->get('/overview');

        $response->assertOk()->assertViewHas('priorityAnomalies', [])->assertViewHas('openAnomalies', 0);
        $response->assertSee('No open anomalies in the available observations')->assertDontSee('Review required');
    }

    public function test_empty_print_report_does_not_claim_a_current_acquisition(): void
    {
        $response = $this->get('/reports/print?period=empty&parameter=water');

        $response->assertOk()->assertSee('No acquisition in selected period')->assertSee('No anomaly assessment can be made');
        $response->assertDontSee('17 Oct, 10:45 MYT');
    }

    public function test_review_return_retains_aoi_period_parameter_and_reference(): void
    {
        $response = $this->post('/alerts/ANM-025/review?aoi=coastal&period=week&parameter=air&reference=annual', ['status' => 'Reviewed', 'note' => 'Compared repeat acquisitions with regional conditions.']);

        $response->assertRedirectToRoute('iems.screen', ['screen' => 'alerts', 'anomaly' => 'ANM-025', 'aoi' => 'coastal', 'period' => 'week', 'parameter' => 'air', 'reference' => 'annual']);
        $response->assertSessionHas('iems.reviews.ANM-025.status', 'Reviewed');
    }

    public function test_unknown_report_template_is_rejected_before_preview(): void
    {
        $this->getJson('/reports?type=unknown')->assertUnprocessable()->assertJsonValidationErrors('type');
    }

    public function test_custom_range_filters_acquisitions_metrics_and_anomalies(): void
    {
        $response = $this->get('/overview?period=custom&start_date=2026-10-14&end_date=2026-10-18');

        $response->assertOk()->assertViewHas('hasData', true);
        $response->assertViewHas('observations', fn (array $rows): bool => count($rows) === 9 && min(array_column($rows, 'date')) === '2026-10-14' && max(array_column($rows, 'date')) === '2026-10-18');
        $response->assertViewHas('anomalies', fn (array $rows): bool => array_column($rows, 'id') === ['ANM-025', 'ANM-024']);
        $response->assertViewHas('latestAcquisition', '18 Oct 2026, 14:20 MYT');
        $response->assertViewHas('metrics', fn (array $metrics): bool => $metrics['methane']['value'] === '1,968' && $metrics['methane']['latestDate'] === '18 Oct 2026');
    }

    public function test_same_day_range_is_inclusive_and_does_not_invent_water_data(): void
    {
        $response = $this->get('/overview?period=custom&start_date=2026-10-09&end_date=2026-10-09');

        $response->assertOk()->assertViewHas('periodLabel', '9 Oct 2026');
        $response->assertViewHas('observations', fn (array $rows): bool => count($rows) === 2);
        $response->assertViewHas('metrics', fn (array $metrics): bool => $metrics['water']['available'] === false && $metrics['methane']['available'] === true);
        $response->assertViewHas('anomalies', fn (array $rows): bool => array_column($rows, 'id') === ['ANM-023']);
    }

    #[TestWith(['2026-10-20', '2026-10-20'])]
    #[TestWith(['2027-01-01', '2027-03-31'])]
    public function test_custom_range_without_acquisitions_shows_no_data(string $start, string $end): void
    {
        $response = $this->get('/overview?period=custom&start_date='.$start.'&end_date='.$end);

        $response->assertOk()->assertViewHas('hasData', false)->assertViewHas('observations', [])->assertViewHas('anomalies', []);
        $response->assertSee('No observations available')->assertDontSee('No anomalies detected');
    }

    #[TestWith(['', '2026-10-18', 'start_date'])]
    #[TestWith(['2026-10-14', '', 'end_date'])]
    #[TestWith(['14/10/2026', '2026-10-18', 'start_date'])]
    #[TestWith(['2026-02-30', '2026-10-18', 'start_date'])]
    #[TestWith(['2026-10-18', '2026-10-14', 'end_date'])]
    public function test_custom_range_rejects_missing_invalid_or_reversed_dates(string $start, string $end, string $field): void
    {
        $this->getJson('/overview?period=custom&start_date='.$start.'&end_date='.$end)
            ->assertUnprocessable()->assertJsonValidationErrors($field);
    }

    public function test_custom_dates_carry_to_report_export_and_aoi_navigation(): void
    {
        $query = ['aoi' => 'coastal', 'period' => 'custom', 'parameter' => 'water', 'reference' => 'annual', 'start_date' => '2026-10-14', 'end_date' => '2026-10-16'];

        $response = $this->get(route('iems.screen', ['screen' => 'reports'] + $query));

        $response->assertOk()->assertSee(route('iems.report', ['type' => 'findings'] + $query));
        $print = $this->get(route('iems.report', $query));
        $print->assertOk()->assertViewHas('startDate', '2026-10-14')->assertViewHas('endDate', '2026-10-16')->assertSee('16 Oct 2026, 10:45 MYT');
        $csv = $this->get(route('iems.export', $query));
        $csv->assertOk();
        $this->assertStringContainsString('14 Oct 2026', $csv->streamedContent());
        $this->assertStringNotContainsString('17 Oct 2026', $csv->streamedContent());
        $this->get(route('iems.screen', ['screen' => 'areas-of-interest'] + $query))->assertSee(route('iems.screen', ['screen' => 'areas-of-interest', 'aoi' => 'catchment'] + $query));
    }

    public function test_review_return_keeps_custom_calendar_dates(): void
    {
        $query = ['aoi' => 'coastal', 'period' => 'custom', 'parameter' => 'air', 'reference' => 'seasonal', 'start_date' => '2026-10-17', 'end_date' => '2026-10-18'];

        $response = $this->post(route('iems.review', ['anomaly' => 'ANM-025'] + $query), ['status' => 'Reviewed', 'note' => 'Checked repeat acquisitions within the selected range.']);

        $response->assertRedirectToRoute('iems.screen', ['screen' => 'alerts', 'anomaly' => 'ANM-025'] + $query);
        $response->assertSessionHas('iems.reviews.ANM-025.status', 'Reviewed');
    }
}
