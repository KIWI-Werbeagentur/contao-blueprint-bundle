/**
 * Blueprint Preview Integration mit Contao Live Preview Sidebar
 * Navigates the CLP iframe to the correct page on hover and
 * loads blueprint content via Turbo-Frames.
 */

let hoverGeneration = 0;
let navigationGeneration = 0;
let abortController = null;
let currentPageUrl = null;
let isNavigating = false;
let pendingBlueprint = null;
let loadingCount = 0;
let spinnerOverlay = null;

function ensureSpinner() {
    if (spinnerOverlay) return spinnerOverlay;
    const wrap = document.getElementById('clp-frame-wrap');
    if (!wrap) return null;
    spinnerOverlay = document.createElement('div');
    spinnerOverlay.className = 'bp-spinner-overlay';
    spinnerOverlay.innerHTML = '<div class="bp-spinner"></div>';
    wrap.appendChild(spinnerOverlay);
    return spinnerOverlay;
}

function showSpinner() {
    loadingCount++;
    var el = ensureSpinner();
    if (el) el.classList.add('is-active');
}

function hideSpinner() {
    loadingCount = Math.max(0, loadingCount - 1);
    if (loadingCount === 0 && spinnerOverlay) {
        spinnerOverlay.classList.remove('is-active');
    }
}

function getCleanUrl(url) {
    try {
        const u = new URL(url);
        u.searchParams.delete('_clp');
        u.searchParams.delete('_t');
        return u.toString();
    } catch {
        return url;
    }
}

function ensureSidebarOpen(clpFrame, callback) {
    const toggleBtn = document.getElementById('clp-toggle-btn');
    if (toggleBtn && !document.body.classList.contains('clp-open')) {
        toggleBtn.click();
        function onInitialLoad() {
            clpFrame.removeEventListener('load', onInitialLoad);
            callback();
        }
        clpFrame.addEventListener('load', onInitialLoad);
    } else {
        callback();
    }
}

function navigateToPage(clpFrame, targetUrl, signal, callback) {
    const cleanTarget = getCleanUrl(targetUrl);
    const cleanCurrent = clpFrame.src ? getCleanUrl(clpFrame.src) : '';

    if (cleanTarget === cleanCurrent) {
        callback();
        return;
    }

    const gen = ++navigationGeneration;
    isNavigating = true;
    showSpinner();

    function onLoad() {
        if (gen !== navigationGeneration) return;
        clpFrame.removeEventListener('load', onLoad);
        isNavigating = false;
        hideSpinner();
        currentPageUrl = cleanTarget;
        callback();
    }

    clpFrame.addEventListener('load', onLoad);
    clpFrame.src = targetUrl + '?_clp=1';
}

function loadBlueprintContent(clpFrame, pageId, alias, afterArticle, framePosition) {
    const url = new URL(window.strBlueprintPreview, window.location.origin);
    url.searchParams.set('page', pageId);
    url.searchParams.set('alias', alias);
    if (afterArticle !== '0') {
        url.searchParams.set('afterArticle', afterArticle);
    }

    const frameDoc = clpFrame.contentDocument;
    if (!frameDoc) return;

    const frameId = 'bp-insert-' + pageId + '-' + framePosition;
    const turboFrame = frameDoc.getElementById(frameId);
    if (!turboFrame) return;

    turboFrame.addEventListener('turbo:frame-load', function onLoaded() {
        turboFrame.removeEventListener('turbo:frame-load', onLoaded);
        turboFrame.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });

    turboFrame.src = url.toString();

    // Fallback: ensure scroll even if event was missed
    setTimeout(function () {
        if (turboFrame.innerHTML.trim().length > 0) {
            turboFrame.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }, 500);
}

function initBlueprintPreviews() {
    if (abortController) {
        abortController.abort();
    }
    abortController = new AbortController();
    const signal = abortController.signal;

    const clpFrame = document.getElementById('clp-frame');
    if (!clpFrame) {
        console.debug('Contao Live Preview not available - Blueprint preview disabled');
        return;
    }

    if (!currentPageUrl && clpFrame.src) {
        currentPageUrl = getCleanUrl(clpFrame.src);
    }

    // Paste icon hover — navigate to that page
    const wrappers = document.querySelectorAll('.add_blueprint__wrapper[data-page-url]');
    wrappers.forEach(function (wrapper) {
        wrapper.addEventListener('mouseenter', function () {
            const pageUrl = wrapper.dataset.pageUrl;
            const pageId = wrapper.dataset.page;
            if (!pageUrl) return;

            var currentGen = ++hoverGeneration;

            function doNavigate() {
                if (hoverGeneration !== currentGen) return;

                navigateToPage(clpFrame, pageUrl, signal, function () {
                    if (hoverGeneration !== currentGen) return;

                    if (pendingBlueprint && pendingBlueprint.pageId === pageId) {
                        var pb = pendingBlueprint;
                        pendingBlueprint = null;
                        loadBlueprintContent(clpFrame, pb.pageId, pb.alias, pb.afterArticle, pb.framePosition);
                    }
                });
            }

            ensureSidebarOpen(clpFrame, doNavigate);
        }, { signal: signal });
    });

    // Blueprint name hover — load content into turbo-frame
    var triggers = document.querySelectorAll('[data-blueprint-alias]');
    triggers.forEach(function (trigger) {
        trigger.addEventListener('mouseenter', function () {
            var currentGen = ++hoverGeneration;

            var wrapper = trigger.closest('[data-page]');
            if (!wrapper) return;

            var pageId = wrapper.dataset.page;
            var alias = trigger.dataset.blueprintAlias;
            var afterArticle = trigger.dataset.afterArticle || '0';
            var framePosition = trigger.dataset.position || '0';
            var pageUrl = wrapper.dataset.pageUrl;

            function doLoad() {
                if (hoverGeneration !== currentGen) return;
                loadBlueprintContent(clpFrame, pageId, alias, afterArticle, framePosition);
            }

            function doNavigateThenLoad() {
                if (hoverGeneration !== currentGen) return;

                if (pageUrl) {
                    var cleanTarget = getCleanUrl(pageUrl);
                    var cleanCurrent = clpFrame.src ? getCleanUrl(clpFrame.src) : '';

                    if (cleanTarget !== cleanCurrent) {
                        pendingBlueprint = {
                            pageId: pageId,
                            alias: alias,
                            afterArticle: afterArticle,
                            framePosition: framePosition
                        };
                        navigateToPage(clpFrame, pageUrl, signal, function () {
                            if (hoverGeneration !== currentGen) return;
                            var pb = pendingBlueprint;
                            if (pb && pb.pageId === pageId) {
                                pendingBlueprint = null;
                                loadBlueprintContent(clpFrame, pb.pageId, pb.alias, pb.afterArticle, pb.framePosition);
                            }
                        });
                        return;
                    }
                }

                doLoad();
            }

            if (isNavigating) {
                pendingBlueprint = {
                    pageId: pageId,
                    alias: alias,
                    afterArticle: afterArticle,
                    framePosition: framePosition
                };
                return;
            }

            ensureSidebarOpen(clpFrame, doNavigateThenLoad);
        }, { signal: signal });

        // No mouseleave handler — preview persists
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initBlueprintPreviews);
} else {
    initBlueprintPreviews();
}

document.addEventListener('turbo:load', initBlueprintPreviews);
document.addEventListener('turbo:render', initBlueprintPreviews);
