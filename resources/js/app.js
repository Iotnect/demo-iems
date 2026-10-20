document.querySelector('[data-toggle-password]')?.addEventListener('click', event => {
    const button = event.currentTarget;
    const password = document.getElementById('password');
    const visible = password.type === 'password';
    password.type = visible ? 'text' : 'password';
    button.setAttribute('aria-pressed', String(visible));
    button.setAttribute('aria-label', visible ? 'Hide password' : 'Show password');
    button.querySelector('[data-password-show]').hidden = visible;
    button.querySelector('[data-password-hide]').hidden = !visible;
});
document.querySelector('[data-login-form]')?.addEventListener('submit', event => {
    const button = event.currentTarget.querySelector('[type="submit"]');
    button.disabled = true;
    button.querySelector('[data-login-label]').textContent = 'Signing in…';
});
window.addEventListener('pageshow', () => {
    const button = document.querySelector('[data-login-form] [type="submit"]');
    if (button) {
        button.disabled = false;
        button.querySelector('[data-login-label]').textContent = 'Sign in to IEMS';
    }
});

document.querySelectorAll('[data-open-dialog]').forEach(button => button.addEventListener('click', () => document.getElementById(button.dataset.openDialog)?.showModal()));
document.querySelectorAll('[data-close-dialog]').forEach(button => button.addEventListener('click', () => button.closest('dialog')?.close()));
document.querySelectorAll('dialog').forEach(dialog => dialog.addEventListener('click', event => {
    if (event.target !== dialog) return;
    const bounds = dialog.getBoundingClientRect();
    if (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom) dialog.close();
}));
document.querySelectorAll('[data-auto-submit]').forEach(select => select.addEventListener('change', () => {
    document.querySelector('.loading-overlay')?.removeAttribute('hidden');
    select.form.requestSubmit();
}));
window.addEventListener('pageshow', () => document.querySelector('.loading-overlay')?.setAttribute('hidden', ''));
document.querySelector('[data-toggle-sidebar]')?.addEventListener('click', event => {
    event.stopPropagation();
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('open');
    event.currentTarget.setAttribute('aria-expanded', sidebar.classList.contains('open'));
});
const closeSidebar = () => {
    document.getElementById('sidebar')?.classList.remove('open');
    document.querySelector('[data-toggle-sidebar]')?.setAttribute('aria-expanded', 'false');
};
document.querySelector('[data-close-sidebar]')?.addEventListener('click', () => {
    closeSidebar();
    document.querySelector('[data-toggle-sidebar]')?.focus();
});

document.addEventListener('click', event => {
    if (!event.target.closest('.sidebar, [data-toggle-sidebar]')) closeSidebar();
    if (!event.target.closest('.notification-wrap')) {
        document.querySelector('.notification-popover')?.setAttribute('hidden', '');
        document.querySelector('[data-toggle-notifications]')?.setAttribute('aria-expanded', 'false');
    }
});
document.querySelector('[data-toggle-notifications]')?.addEventListener('click', event => {
    const panel = document.querySelector('.notification-popover');
    panel.hidden = !panel.hidden;
    event.currentTarget.setAttribute('aria-expanded', String(!panel.hidden));
});
document.querySelectorAll('[data-dismiss]').forEach(button => button.addEventListener('click', () => button.closest('.notice').remove()));
const selectTrend = (section, key) => {
    section.querySelectorAll('[data-trend-tab]').forEach(tab => {
        const active = tab.dataset.trendTab === key;
        tab.classList.toggle('active', active);
        tab.setAttribute('aria-selected', String(active));
        tab.tabIndex = active ? 0 : -1;
    });
    section.querySelectorAll('[data-trend-panel]').forEach(panel => panel.hidden = panel.dataset.trendPanel !== key);
};
document.querySelectorAll('[data-trend-tabs]').forEach(section => {
    const tabs = [...section.querySelectorAll('[data-trend-tab]')];
    selectTrend(section, tabs.find(tab => tab.getAttribute('aria-selected') === 'true')?.dataset.trendTab);
    tabs.forEach((button, index) => {
        button.addEventListener('click', () => selectTrend(section, button.dataset.trendTab));
        button.addEventListener('keydown', event => {
            let next;
            if (event.key === 'ArrowRight') next = (index + 1) % tabs.length;
            if (event.key === 'ArrowLeft') next = (index - 1 + tabs.length) % tabs.length;
            if (event.key === 'Home') next = 0;
            if (event.key === 'End') next = tabs.length - 1;
            if (next === undefined) return;
            event.preventDefault();
            selectTrend(section, tabs[next].dataset.trendTab);
            tabs[next].focus();
        });
    });
});
document.querySelectorAll('[data-chart]').forEach(chart => {
    const tooltip = chart.querySelector('.chart-tooltip');
    chart.querySelectorAll('[data-chart-value]').forEach(point => {
        const show = () => { tooltip.textContent = point.dataset.chartValue; tooltip.classList.add('visible'); };
        point.addEventListener('mouseenter', show);
        point.addEventListener('focus', show);
        point.addEventListener('mouseleave', () => tooltip.classList.remove('visible'));
        point.addEventListener('blur', () => tooltip.classList.remove('visible'));
    });
});
document.querySelectorAll('[data-map]').forEach(map => {
    let zoom = 1.55, panX = 0, panY = 0;
    const group = map.querySelector('.map-zoom-group');
    const canvas = map.querySelector('.map-canvas');
    const focusX = Number(map.dataset.focusX), focusY = Number(map.dataset.focusY);
    const clamp = (value, min, max) => Math.max(min, Math.min(max, value));
    const transform = () => {
        panX = clamp(panX, -(zoom - 1) * 1024, 0);
        panY = clamp(panY, -(zoom - 1) * 512, 0);
        group.setAttribute('transform', `translate(${panX} ${panY}) scale(${zoom})`);
        map.querySelector('[data-map-scale]').textContent = `${(10 / zoom).toFixed(1)} km`;
    };
    const fit = () => {
        zoom = 1.55;
        panX = 512 - focusX * zoom;
        panY = 256 - focusY * zoom;
        transform();
    };
    fit();
    map.querySelectorAll('[data-map-zoom]').forEach(button => button.addEventListener('click', () => {
        const next = clamp(zoom + (button.dataset.mapZoom === 'in' ? .35 : -.35), 1, 3.5);
        panX = 512 - (512 - panX) * next / zoom;
        panY = 256 - (256 - panY) * next / zoom;
        zoom = next;
        transform();
    }));
    map.querySelector('[data-map-reset]')?.addEventListener('click', fit);
    map.querySelector('[data-toggle-layers]')?.addEventListener('click', event => {
        const options = map.querySelector('.layer-options');
        options.hidden = !options.hidden;
        event.currentTarget.setAttribute('aria-expanded', String(!options.hidden));
    });
    const selection = map.querySelector('[data-map-selection]');
    const originalSelection = selection?.innerHTML;
    map.querySelectorAll('[data-layer-toggle]').forEach(toggle => toggle.addEventListener('change', () => {
        map.querySelectorAll(`[data-map-layer="${toggle.dataset.layerToggle}"]`).forEach(layer => layer.style.display = toggle.checked ? '' : 'none');
        if (!toggle.checked && map.querySelector(`[data-map-marker].is-selected[data-map-layer="${toggle.dataset.layerToggle}"]`)) {
            selection.innerHTML = originalSelection;
            map.querySelectorAll('[data-map-marker]').forEach(marker => marker.classList.remove('is-selected'));
            document.querySelectorAll('[data-alert-row]').forEach(row => row.classList.remove('is-selected'));
        }
    }));
    map.addEventListener('iems:select', event => {
        const marker = [...map.querySelectorAll('[data-map-marker]')].find(item => item.dataset.selectAnomaly === event.detail.id);
        if (!marker || !selection) return;
        const data = marker.dataset;
        map.querySelectorAll('[data-map-marker]').forEach(item => item.classList.toggle('is-selected', item === marker));
        const toggle = map.querySelector(`[data-layer-toggle="${data.anomalyParameter}"]`);
        if (toggle && !toggle.checked) { toggle.checked = true; toggle.dispatchEvent(new Event('change')); }
        selection.querySelector('.eyebrow').textContent = `${data.selectAnomaly} / ${data.anomalySeverity} priority`;
        selection.querySelector('strong').textContent = data.anomalyTitle;
        selection.querySelector('p').textContent = data.anomalyTime;
        const values = selection.querySelectorAll('.map-selection-stats span');
        values[0].replaceChildren(Object.assign(document.createElement('b'), {textContent: data.anomalyValue}), document.createTextNode('flagged observation'));
        values[1].replaceChildren(Object.assign(document.createElement('b'), {textContent: data.anomalyDeviation}), document.createTextNode('vs. seasonal reference'));
        let confidence = selection.querySelector('[data-selection-confidence]');
        if (!confidence) {
            confidence = document.createElement('p');
            confidence.dataset.selectionConfidence = '';
            selection.querySelector('.map-selection-stats').after(confidence);
        }
        confidence.textContent = `Confidence: ${data.anomalyConfidence}`;
        const link = selection.querySelector('a');
        link.href = marker.href.baseVal;
        link.firstChild.textContent = 'Review observation ';
        if (document.getElementById('environmental-trends') && !selection.querySelector('[data-view-trend]')) {
            const trendLink = document.createElement('a');
            trendLink.href = '#environmental-trends';
            trendLink.dataset.viewTrend = '';
            trendLink.textContent = 'View related AOI trend';
            selection.append(trendLink);
        }
        document.querySelectorAll('[data-alert-row]').forEach(row => row.classList.toggle('is-selected', row.dataset.alertRow === data.selectAnomaly));
        document.querySelectorAll('[data-trend-tabs]').forEach(section => selectTrend(section, data.anomalyParameter));
    });
    let drag = null;
    canvas.addEventListener('pointerdown', event => {
        if (event.target.closest('[data-map-marker]')) return;
        drag = {x: event.clientX, y: event.clientY, panX, panY};
        canvas.setPointerCapture(event.pointerId);
    });
    canvas.addEventListener('pointermove', event => {
        if (!drag) return;
        const scale = Math.max(1024 / canvas.clientWidth, 512 / canvas.clientHeight);
        panX = drag.panX + (event.clientX - drag.x) * scale;
        panY = drag.panY + (event.clientY - drag.y) * scale;
        transform();
    });
    canvas.addEventListener('pointerup', () => drag = null);
    canvas.addEventListener('pointercancel', () => drag = null);
});
document.querySelectorAll('[data-select-anomaly]').forEach(control => {
    const select = event => {
        const map = control.closest('[data-map]') ?? document.querySelector('[data-map]');
        if (!map?.querySelector('[data-map-selection]')) return;
        event.preventDefault();
        map.dispatchEvent(new CustomEvent('iems:select', {detail: {id: control.dataset.selectAnomaly}}));
    };
    control.addEventListener('click', select);
    control.addEventListener('keydown', event => {
        if (control.matches('a') && event.key === ' ') select(event);
    });
});
document.querySelector('[data-open-on-error]')?.showModal();

const reviewPanel = document.querySelector('.anomaly-detail');
if (reviewPanel) {
    let scheduled = false;
    const fitReviewPanel = () => {
        if (window.innerWidth < 1280) {
            reviewPanel.style.maxHeight = '';
        } else {
            const top = Math.max(172, reviewPanel.getBoundingClientRect().top);
            reviewPanel.style.maxHeight = `${Math.max(340, Math.min(820, window.innerHeight - top - 20))}px`;
        }
        scheduled = false;
    };
    const scheduleFit = () => {
        if (!scheduled) {
            scheduled = true;
            requestAnimationFrame(fitReviewPanel);
        }
    };
    window.addEventListener('scroll', scheduleFit, {passive: true});
    window.addEventListener('resize', scheduleFit);
    fitReviewPanel();
}

document.querySelectorAll('[data-expand-map]').forEach(button => button.addEventListener('click', () => {
    const expanded = button.closest('.map-panel').classList.toggle('expanded');
    button.setAttribute('aria-label', expanded ? 'Close expanded map' : 'Expand monitoring map');
    document.body.style.overflow = expanded ? 'hidden' : '';
}));
document.addEventListener('keydown', event => {
    if (event.key !== 'Escape') return;
    document.querySelectorAll('.map-panel.expanded').forEach(panel => panel.classList.remove('expanded'));
    document.body.style.overflow = '';
    closeSidebar();
});
document.querySelectorAll('[data-observations]').forEach(section => section.querySelector('[data-table-search]')?.addEventListener('input', event => {
    const search = event.target.value.trim().toLowerCase();
    let count = 0;
    section.querySelectorAll('[data-search-row]').forEach(row => { row.hidden = !row.textContent.toLowerCase().includes(search); if (!row.hidden) count++; });
    section.querySelector('[data-search-empty]').hidden = count > 0;
}));
document.querySelectorAll('[data-print-report]').forEach(button => button.addEventListener('click', () => window.print()));

document.querySelectorAll('[data-period-picker]').forEach(picker => {
    const details = picker.querySelector('details');
    const start = picker.querySelector('[data-range-start]');
    const end = picker.querySelector('[data-range-end]');
    start.addEventListener('change', () => { end.min = start.value; });
    details.addEventListener('toggle', () => {
        if (details.open) {
            document.querySelectorAll('[data-period-picker] details').forEach(other => {
                if (other !== details) other.open = false;
            });
        }
    });
    picker.addEventListener('keydown', event => {
        if (event.key === 'Escape' && details.open) {
            details.open = false;
            picker.querySelector('summary').focus();
        }
    });
});
document.addEventListener('click', event => {
    document.querySelectorAll('[data-period-picker]').forEach(picker => {
        if (!picker.contains(event.target)) picker.querySelector('details').open = false;
    });
});
