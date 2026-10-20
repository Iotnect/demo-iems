@props(['period', 'startDate', 'endDate', 'periodLabel', 'id' => 'observation-range'])
<div {{ $attributes->merge(['class' => 'period-picker']) }} data-period-picker>
    <input type="hidden" name="period" value="{{ $period }}" data-period-value>
    <details class="period-picker-details">
        <summary aria-label="Choose observation period"><span>{{ $periodLabel }}</span><x-icon name="down" size="13"/></summary>
        <div class="period-picker-panel">
            <strong>Choose a date range</strong>
            <div class="period-date-fields">
                <label for="{{ $id }}-start">Start date<input type="date" id="{{ $id }}-start" name="start_date" value="{{ $startDate }}" required data-range-start></label>
                <label for="{{ $id }}-end">End date<input type="date" id="{{ $id }}-end" name="end_date" value="{{ $endDate }}" min="{{ $startDate }}" required data-range-end></label>
            </div>
            <p>Dates are inclusive. Demonstration observations are available in October 2026.</p>
            <button type="submit" name="period" value="custom" class="button button-primary full-width" data-apply-range>Apply range</button>
            <div class="period-presets"><span>Quick ranges</span>
                <button type="submit" name="period" value="month" formnovalidate>1–20 Oct 2026</button>
                <button type="submit" name="period" value="week" formnovalidate>14–20 Oct 2026</button>
                <button type="submit" name="period" value="empty" formnovalidate>September 2026</button>
            </div>
        </div>
    </details>
</div>
