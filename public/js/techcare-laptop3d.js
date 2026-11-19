/* Techcare 3D laptop: gentle sway + drag / arrow keys to rotate. No dependencies. */
(function () {
    'use strict';

    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function clamp(v, lo, hi) { return Math.max(lo, Math.min(hi, v)); }

    function init(el) {
        var rx = parseFloat(el.getAttribute('data-rx')) || -18;
        var ry0 = parseFloat(el.getAttribute('data-ry')) || -28;
        var user = 0, sway = 0;
        var dragging = false, hover = false, visible = true, raf = null;
        var lastX = 0, lastY = 0, t0 = performance.now();

        function apply() {
            el.style.setProperty('--rx', rx + 'deg');
            el.style.setProperty('--ry', (ry0 + user + sway) + 'deg');
        }

        function frame(now) {
            raf = null;
            if (!dragging && !hover) { sway = Math.sin((now - t0) / 1900) * 24; apply(); }
            if (visible && !reduce) { raf = requestAnimationFrame(frame); }
        }
        function start() { if (!raf && visible && !reduce) { raf = requestAnimationFrame(frame); } }

        el.addEventListener('pointerdown', function (e) {
            dragging = true; lastX = e.clientX; lastY = e.clientY;
            el.classList.add('is-grabbing');
            if (el.setPointerCapture) { try { el.setPointerCapture(e.pointerId); } catch (err) {} }
        });
        el.addEventListener('pointermove', function (e) {
            if (!dragging) { return; }
            user += (e.clientX - lastX) * 0.6;
            rx = clamp(rx - (e.clientY - lastY) * 0.35, -70, -4);
            lastX = e.clientX; lastY = e.clientY;
            apply();
        });
        function release() { dragging = false; el.classList.remove('is-grabbing'); start(); }
        el.addEventListener('pointerup', release);
        el.addEventListener('pointercancel', release);
        el.addEventListener('mouseenter', function () { hover = true; });
        el.addEventListener('mouseleave', function () { hover = false; start(); });
        el.addEventListener('dblclick', function () { user = 0; rx = parseFloat(el.getAttribute('data-rx')) || -18; sway = 0; apply(); });

        el.addEventListener('keydown', function (e) {
            var k = e.key, handled = true;
            if (k === 'ArrowLeft') { user -= 10; }
            else if (k === 'ArrowRight') { user += 10; }
            else if (k === 'ArrowUp') { rx = clamp(rx - 5, -70, -4); }
            else if (k === 'ArrowDown') { rx = clamp(rx + 5, -70, -4); }
            else { handled = false; }
            if (handled) { e.preventDefault(); apply(); }
        });

        if ('IntersectionObserver' in window) {
            new IntersectionObserver(function (entries) {
                visible = entries[0].isIntersecting; if (visible) { start(); }
            }).observe(el);
        }
        document.addEventListener('visibilitychange', function () {
            visible = !document.hidden; if (visible) { start(); }
        });

        apply();
        start();
    }

    function boot() { Array.prototype.forEach.call(document.querySelectorAll('[data-l3d]'), init); }
    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', boot); } else { boot(); }
})();
