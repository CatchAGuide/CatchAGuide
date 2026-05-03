@props(['pageContext' => null])
@php
    $enabled = (bool) config('booking_assistant.enabled');
    if (! $enabled) {
        return;
    }
@endphp
@php
    $ctx = trim((string) ($pageContext ?? ''));
    if ($ctx === '') {
        $route = request()->route();
        $name = $route ? $route->getName() : null;
        $ctx = $name ? (string) $name : request()->path();
    }
    $browseGuidingsUrl = \Illuminate\Support\Facades\Route::has('guidings.index')
        ? route('guidings.index')
        : url('/guidings');
    $bookingAssistantWelcomeSuggestions = [
        'Help me plan a guided fishing trip',
        'Show packages, camps, or guidings for my dates',
        "What's included in a guiding package?",
    ];
@endphp
<div id="booking-assistant-root" data-endpoint="{{ url('/assistant/chat') }}" data-csrf="{{ csrf_token() }}" data-page-context="{{ e($ctx) }}">
    <button type="button" class="booking-assistant-fab" id="booking-assistant-toggle" aria-expanded="false" aria-controls="booking-assistant-modal" title="{{ __('booking-assistant.widget_open') }}">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M20 2H4a2 2 0 0 0-2 2v18l4-4h14a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
            <path d="M7 9h10M7 13h6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
        </svg>
    </button>
    <button type="button" class="booking-assistant-teaser" id="booking-assistant-teaser" aria-hidden="true" tabindex="-1">
        <span class="ba-teaser__bubble" aria-hidden="true">
            <span class="ba-teaser__title">Need help?<span class="ba-teaser__dots" aria-hidden="true"><i></i><i></i><i></i></span></span>
            <span class="ba-teaser__sub">Ask your fishing guide</span>
            <span class="ba-teaser__nudge" aria-hidden="true">Tap to chat</span>
        </span>
    </button>
    <div id="booking-assistant-modal" class="booking-assistant-modal ba-state--splash" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="booking-assistant-title">
        <div class="booking-assistant-modal__shell">
            <header class="booking-assistant-head">
                <div class="ba-brand">
                    <span id="booking-assistant-title">{{ __('booking-assistant.widget_title') }}</span>
                    <span class="ba-brand__beta" aria-label="{{ __('booking-assistant.widget_beta') }}">{{ __('booking-assistant.widget_beta') }}</span>
                </div>
                <div class="ba-head-actions">
                    <button type="button" class="ba-icon-btn" id="booking-assistant-clear" title="{{ __('booking-assistant.widget_clear') }}" aria-label="{{ __('booking-assistant.widget_clear') }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                            <path d="M9 3h6m-8 4h10m-1 0-.7 13.2A2 2 0 0 1 13.3 22h-2.6a2 2 0 0 1-2-1.8L8 7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                            <path d="M10 11v7M14 11v7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" opacity="0.9"/>
                        </svg>
                    </button>
                    <button type="button" class="ba-icon-btn" id="booking-assistant-close" title="{{ __('booking-assistant.widget_close') }}" aria-label="{{ __('booking-assistant.widget_close') }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                            <path d="M7 7l10 10M17 7 7 17" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
            </header>
            <div class="booking-assistant-modal__body">
                <div class="booking-assistant-modal__scroll" id="booking-assistant-scroll">
                    <div class="booking-assistant-modal__col booking-assistant-modal__col--content">
                        <div class="ba-welcome" id="booking-assistant-welcome">
                            <div class="ba-spotlight" id="booking-assistant-spotlight" aria-live="polite">
                                <span class="ba-spotlight__text">{{ __('booking-assistant.widget_spotlight') }}</span>
                                <button type="button" class="ba-spotlight__close" id="booking-assistant-spotlight-dismiss" aria-label="{{ __('booking-assistant.widget_spotlight_dismiss') }}" title="{{ __('booking-assistant.widget_spotlight_dismiss') }}">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7 7l10 10M17 7 7 17" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                </button>
                            </div>
                            <h2 class="ba-welcome__title">{{ __('booking-assistant.widget_welcome_title') }}</h2>
                            <p class="ba-welcome__tag">{{ __('booking-assistant.widget_welcome_tagline') }}</p>
                        </div>
                        <div class="ba-chip-strip">
                            <div class="booking-assistant-chip-row-inner" id="booking-assistant-chip-row" aria-label="Suggestions"></div>
                        </div>
                        <div class="booking-assistant-body" id="booking-assistant-messages" aria-live="polite"></div>
                    </div>
                </div>
                <div class="booking-assistant-modal__under-scroll">
                    <div class="booking-assistant-modal__col booking-assistant-modal__col--content">
                        <form class="booking-assistant-foot" id="booking-assistant-form">
                            <div class="booking-assistant-composer">
                                <div class="booking-assistant-composer__field">
                                    <textarea id="booking-assistant-input" rows="1" maxlength="2000" placeholder="{{ __('booking-assistant.widget_placeholder') }}" autocomplete="off"></textarea>
                                </div>
                                <div class="booking-assistant-composer__dock">
                                    <span class="ba-composer-hints" aria-hidden="true">{{ __('booking-assistant.widget_composer_hints') }}</span>
                                    <button type="submit" class="send" id="booking-assistant-send" title="{{ __('booking-assistant.widget_send') }}" aria-label="{{ __('booking-assistant.widget_send') }}">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                                            <path d="M12 18V8M16 11l-4-4-4 4" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </form>
                        <div class="ba-secondary-cta-wrap">
                            <a class="ba-secondary-cta" href="{{ $browseGuidingsUrl }}">{{ __('booking-assistant.widget_browse_cta') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
(function () {
    var root = document.getElementById('booking-assistant-root');
    if (!root) return;
    var endpoint = root.getAttribute('data-endpoint');
    var csrf = root.getAttribute('data-csrf');
    var pageContext = root.getAttribute('data-page-context') || '';
    var modalRoot = document.getElementById('booking-assistant-modal');
    var toggle = document.getElementById('booking-assistant-toggle');
    var clearBtn = document.getElementById('booking-assistant-clear');
    var closeBtn = document.getElementById('booking-assistant-close');
    var messagesEl = document.getElementById('booking-assistant-messages');
    var suggestionsEl = document.getElementById('booking-assistant-chip-row');
    var scrollWrap = document.getElementById('booking-assistant-scroll');
    var spotlightEl = document.getElementById('booking-assistant-spotlight');
    var spotlightDismissBtn = document.getElementById('booking-assistant-spotlight-dismiss');
    var form = document.getElementById('booking-assistant-form');
    var input = document.getElementById('booking-assistant-input');
    var sendBtn = document.getElementById('booking-assistant-send');
    var teaserBtn = document.getElementById('booking-assistant-teaser');
    var history = [];
    var listingThumbPlaceholder = @json(asset('images/placeholder_guide.jpg'));

    // sessionStorage is per-tab; localStorage is shared across tabs.
    // We store in both so a new tab can restore the same thread.
    var sessionKey = 'booking-assistant:thread:session:' + (pageContext || 'global');
    var sharedKey = 'booking-assistant:thread:shared';
    var threadTtlMs = 1000 * 60 * 60 * 4; // 4 hours
    
    function scrollToBottom() {
        if (scrollWrap) {
            scrollWrap.scrollTop = scrollWrap.scrollHeight;
        } else if (messagesEl) {
            messagesEl.scrollTop = messagesEl.scrollHeight;
        }
    }

    function saveThread() {
        var payload = {
            v: 2,
            savedAt: Date.now(),
            pageContext: pageContext,
            isOpen: !!(modalRoot && modalRoot.classList && modalRoot.classList.contains('is-open')),
            history: history.slice(-24)
        };
        try {
            sessionStorage.setItem(sessionKey, JSON.stringify(payload));
        } catch (e) {
            // ignore storage failures
        }

        try {
            localStorage.setItem(sharedKey, JSON.stringify(payload));
        } catch (e) {
            // ignore storage failures
        }
    }

    function clearMessagesKeepSuggestions() {
        if (!messagesEl) return;
        // Keep the suggestions container (first child)
        var nodes = Array.prototype.slice.call(messagesEl.querySelectorAll('.booking-assistant-msg'));
        nodes.forEach(function (n) { n.parentNode && n.parentNode.removeChild(n); });
    }

    function restoreThread() {
        var raw = null;
        var decoded = null;
        try {
            raw = sessionStorage.getItem(sessionKey);
        } catch (e) {
            raw = null;
        }
        if (!raw) {
            try {
                raw = localStorage.getItem(sharedKey);
            } catch (e) {
                raw = null;
            }
        }
        if (!raw) return;

        try {
            decoded = JSON.parse(raw);
            if (!decoded || !Array.isArray(decoded.history)) return;
            // Expire old threads
            if (decoded.savedAt && (Date.now() - decoded.savedAt) > threadTtlMs) {
                try { sessionStorage.removeItem(sessionKey); } catch (e) {}
                try { localStorage.removeItem(sharedKey); } catch (e) {}
                return;
            }
            history = decoded.history.slice(-24);
        } catch (e) {
            return;
        }

        // Re-render bubbles from history
        clearMessagesKeepSuggestions();
        if (suggestionsEl) {
            suggestionsEl.style.display = history.length ? 'none' : '';
        }
        history.forEach(function (m) {
            if (!m || !m.role) return;
            if (m.role === 'assistant') {
                var norm = normalizeAssistantPayload(m.content || '', m.ui || null);
                appendBubble('assistant', norm.content, norm.ui);
            } else {
                if (!m.content) return;
                appendBubble(m.role, m.content, m.ui || null);
            }
        });

        // If there's an active session/thread, open the modal by default (especially for new tabs).
        if (history.length) {
            // Respect stored open state if present, otherwise default to open.
            var shouldOpen = true;
            try {
                if (decoded && typeof decoded.isOpen === 'boolean') {
                    shouldOpen = decoded.isOpen;
                }
            } catch (e) {
                shouldOpen = true;
            }
            setOpen(shouldOpen);
        }

        // Ensure we're at the newest message after restore.
        setTimeout(scrollToBottom, 0);
        updateShellState();
    }

    function clearThread() {
        history = [];
        clearMessagesKeepSuggestions();
        if (suggestionsEl) {
            suggestionsEl.style.display = '';
        }
        try { sessionStorage.removeItem(sessionKey); } catch (e) {}
        try { localStorage.removeItem(sharedKey); } catch (e) {}
        saveThread();
        renderSuggestions();
        setTimeout(scrollToBottom, 0);
        updateShellState();
    }

    function escapeHtml(s) {
        return (s || '').replace(/[&<>"']/g, function (c) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c] || c;
        });
    }

    function formatMoney(amount, currency) {
        if (amount === null || amount === undefined || amount === '') return '';
        var n = Number(amount);
        if (!isFinite(n)) return '';
        var cur = (currency || 'EUR').toUpperCase();
        try {
            return new Intl.NumberFormat(undefined, { style: 'currency', currency: cur, maximumFractionDigits: 2 }).format(n);
        } catch (e) {
            return n.toFixed(2) + ' ' + cur;
        }
    }

    var listingsIntro = @json(__('booking-assistant.widget_listings_intro'));

    function resolveAssetUrl(u) {
        if (!u || typeof u !== 'string') return '';
        u = u.trim();
        if (/^https?:\/\//i.test(u)) return u;
        if (u.charAt(0) === '/') return window.location.origin + u;
        return window.location.origin + '/' + u.replace(/^\.\//, '');
    }

    function truncateSnippet(s, max) {
        max = max || 100;
        var t = (s || '').toString().replace(/\s+/g, ' ').trim();
        if (t.length <= max) return t;
        return t.slice(0, max - 1).trim() + '\u2026';
    }

    function stripMarkdownFence(s) {
        var t = (s || '').trim();
        var m = t.match(/^```(?:json)?\s*([\s\S]+?)\s*```$/i);
        return m ? m[1].trim() : t;
    }

    function tryParseJsonEnvelope(raw) {
        var t = stripMarkdownFence(String(raw || '').trim());
        if (!t || t.charAt(0) !== '{') return null;
        try {
            var d = JSON.parse(t);
            if (!d || typeof d !== 'object') return null;
            var ui = null;
            if (d.ui && typeof d.ui === 'object') ui = d.ui;
            else if (Array.isArray(d.cards)) ui = { cards: d.cards };
            var content = '';
            if (typeof d.content === 'string') content = d.content.trim();
            else if (typeof d.message === 'string') content = d.message.trim();
            var hasCards = ui && Array.isArray(ui.cards) && ui.cards.length > 0;
            var hasQr = ui && Array.isArray(ui.quick_replies) && ui.quick_replies.length > 0;
            if (!hasCards && !hasQr && !content) return null;
            if ((hasCards || hasQr) && !content) content = listingsIntro;
            return { content: content || '', ui: (hasCards || hasQr) ? ui : null };
        } catch (e) {
            return null;
        }
    }

    function findBalancedJsonSubstrings(text) {
        var out = [];
        var s = text;
        var i, j, depth, ch, inStr, q, esc;
        for (i = 0; i < s.length; i++) {
            if (s.charAt(i) !== '{') continue;
            depth = 0;
            inStr = false;
            q = '';
            esc = false;
            for (j = i; j < s.length; j++) {
                ch = s.charAt(j);
                if (inStr) {
                    if (esc) { esc = false; continue; }
                    if (ch === '\\') { esc = true; continue; }
                    if (ch === q) inStr = false;
                    continue;
                }
                if (ch === '"' || ch === "'") { inStr = true; q = ch; continue; }
                if (ch === '{') depth++;
                else if (ch === '}') {
                    depth--;
                    if (depth === 0) {
                        var slice = s.slice(i, j + 1);
                        if (slice.length >= 12 && (slice.indexOf('"ui"') !== -1 || slice.indexOf('"cards"') !== -1)) {
                            out.push(slice);
                        }
                        break;
                    }
                }
            }
        }
        out.sort(function (a, b) { return b.length - a.length; });
        return out;
    }

    /** When the model returns invalid/truncated JSON, JSON.parse fails but we can still read the "content" string. */
    function extractJsonContentFieldFromPartialEnvelope(raw) {
        var t = String(raw || '');
        var m = /"content"\s*:\s*"/m.exec(t);
        if (!m) return null;
        var i = m.index + m[0].length;
        var out = '';
        var escape = false;
        for (; i < t.length; i++) {
            var ch = t.charAt(i);
            if (escape) {
                if (ch === 'n') out += '\n';
                else if (ch === 'r') out += '\r';
                else if (ch === 't') out += '\t';
                else if (ch === 'b') out += '\b';
                else if (ch === 'f') out += '\f';
                else if (ch === '"' || ch === '\\' || ch === '/') out += ch;
                else if (ch === 'u' && i + 4 < t.length) {
                    var hex = t.substr(i + 1, 4);
                    if (/^[0-9a-fA-F]{4}$/.test(hex)) {
                        out += String.fromCharCode(parseInt(hex, 16));
                        i += 4;
                    }
                } else out += ch;
                escape = false;
                continue;
            }
            if (ch === '\\') {
                escape = true;
                continue;
            }
            if (ch === '"') break;
            out += ch;
        }
        out = out.replace(/\s{2,}/g, ' ').trim();
        return out || null;
    }

    function normalizeAssistantPayload(content, topUi) {
        var text = (content == null ? '' : String(content)).trim();
        var ui = null;
        if (topUi && typeof topUi === 'object') {
            try {
                ui = JSON.parse(JSON.stringify(topUi));
            } catch (e) {
                ui = topUi;
            }
        }

        var parsed = tryParseJsonEnvelope(text);
        if (parsed) {
            if (parsed.ui) ui = parsed.ui;
            text = parsed.content || '';
        } else {
            var fenced = stripMarkdownFence(text);
            var subs = findBalancedJsonSubstrings(fenced);
            for (var k = 0; k < subs.length; k++) {
                parsed = tryParseJsonEnvelope(subs[k]);
                if (parsed && parsed.ui) {
                    ui = parsed.ui;
                    var prefix = fenced.replace(subs[k], '').trim();
                    text = parsed.content ? parsed.content : (prefix || listingsIntro);
                    break;
                }
            }
        }

        var tail = text.match(/(\{[\s\S]*"ui"[\s\S]*\})\s*$/);
        if (tail && (!ui || !ui.cards || !ui.cards.length)) {
            try {
                var d2 = JSON.parse(tail[1]);
                if (d2 && d2.ui && typeof d2.ui === 'object') {
                    ui = d2.ui;
                    var inner = (typeof d2.content === 'string' ? d2.content.trim() : '');
                    text = inner || text.replace(tail[1], '').trim() || listingsIntro;
                }
            } catch (e2) {}
        }

        if (text.charAt(0) === '{' && (text.indexOf('"ui"') !== -1 || text.indexOf('"cards"') !== -1)) {
            var recovery = tryParseJsonEnvelope(text);
            if (recovery) {
                text = recovery.content || listingsIntro;
                if (recovery.ui) ui = recovery.ui;
            }
        }

        if ((!ui || !ui.cards || !ui.cards.length) && topUi && topUi.cards && topUi.cards.length) {
            ui = ui || {};
            ui.cards = topUi.cards;
        }

        if (ui && Array.isArray(ui.cards) && ui.cards.length > 0) {
            text = stripAllCardJsonBlocks(String(text || ''));
            var cut = text.search(/\{\s*"(?:content|ui|cards)"/);
            if (cut > 0) {
                text = text.slice(0, cut).trim();
            }
            var tr = String(text || '').trim();
            if (tr.charAt(0) === '{' && (tr.indexOf('"ui"') !== -1 || tr.indexOf('"cards"') !== -1)) {
                text = extractJsonContentFieldFromPartialEnvelope(tr) || listingsIntro;
            } else {
                text = tr.replace(/\s{2,}/g, ' ').trim();
                if (!text) text = listingsIntro;
            }
        }

        return { content: text, ui: ui };
    }

    function stripAllCardJsonBlocks(t) {
        var s = String(t || '');
        var guard = 0;
        while (guard++ < 16) {
            var subs = findBalancedJsonSubstrings(s);
            var removed = false;
            for (var i = 0; i < subs.length; i++) {
                var sub = subs[i];
                var p = tryParseJsonEnvelope(sub);
                if (!p || !p.ui || !Array.isArray(p.ui.cards) || !p.ui.cards.length) {
                    continue;
                }
                s = s.split(sub).join('').replace(/\s{2,}/g, ' ').trim();
                removed = true;
                break;
            }
            if (!removed) {
                break;
            }
        }
        return s;
    }

    function stripToolArtifacts(raw) {
        var t = (raw || '').toString();
        // Remove tool-call markup occasionally emitted by some providers
        t = t.replace(/<function\b[^>]*>[\s\S]*?<\/function>/gi, '');
        // Some models emit "<function=search_catalog>{...}</function>" (not valid HTML, but we still strip it)
        t = t.replace(/<function=[^>]*>[\s\S]*?<\/function>/gi, '');
        t = t.replace(/<function\b[^>]*\/>/gi, '');
        t = t.replace(/<tool\b[^>]*>[\s\S]*?<\/tool>/gi, '');
        t = t.replace(/<\/?tool_call\b[^>]*>/gi, '');
        return t.trim();
    }

    function linkifyAndHighlight(text) {
        var clean = stripToolArtifacts(text);
        var html = escapeHtml(clean || '');
        html = html.replace(/\n/g, '<br />');

        // Bold **text**
        html = html.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');

        // URLs (http/https)
        html = html.replace(/(https?:\/\/[^\s<]+)/g, function (m) {
            var url = m.replace(/&amp;/g, '&');
            return '<a class="ba-link" href="' + url + '" target="_blank" rel="noopener noreferrer">' + m + '</a>';
        });

        // Prices (common formats): €303.33, 303.33€, EUR 303.33, 303.33 EUR
        html = html.replace(/((?:€|EUR)\s?\d+(?:[.,]\d{1,2})?)/gi, '<span class="ba-hl ba-price">$1</span>');
        html = html.replace(/(\d+(?:[.,]\d{1,2})?\s?(?:€|EUR))/gi, '<span class="ba-hl ba-price">$1</span>');

        // Simple emphasis for “important” keywords
        html = html.replace(/\b(available|availability|price|from|starting|book|booking|schedule|persons?|people|date|dates)\b/gi, '<span class="ba-hl">$1</span>');

        return html;
    }

    function parseSuggestionLines(text) {
        var clean = stripToolArtifacts(text || '');
        if (!clean) return [];
        var lines = clean.split(/\r?\n/).map(function (l) { return l.trim(); }).filter(Boolean);
        var items = [];

        lines.forEach(function (line) {
            // Match numbered list items: "1. ..." or "1) ..."
            var m = line.match(/^(?:\d+[\.\)]\s+)(.+)$/);
            if (!m) {
                // Match bullets: "- ..." or "• ..."
                m = line.match(/^(?:[-•]\s+)(.+)$/);
            }
            if (!m) return;

            var body = m[1];
            // Never turn JSON blobs into cards
            if (/^\s*\{[\s\S]*\}\s*$/.test(body) || body.trim().charAt(0) === '{') {
                return;
            }
            // Extract title from **Title** if present
            var bold = body.match(/\*\*([^*]+)\*\*/);
            var title = bold ? bold[1].trim() : body.split(/[—–-]/)[0].trim();
            // Cut at ":" too (prevents very long "Title: description..." being used as the title)
            if (!bold && title.indexOf(':') !== -1) {
                title = title.split(':')[0].trim();
            }
            title = title.replace(/^"+|"+$/g, '').trim();
            if (!title) return;

            // Extract URL if present
            var urlMatch = body.match(/(https?:\/\/[^\s]+)/);
            var url = urlMatch ? urlMatch[1] : null;

            // Extract price if present (simple)
            var priceMatch = body.match(/(?:€|EUR)\s?\d+(?:[.,]\d{1,2})?|\d+(?:[.,]\d{1,2})?\s?(?:€|EUR)/i);
            var priceText = priceMatch ? priceMatch[0] : null;

            items.push({
                title: title.slice(0, 70),
                url: url,
                priceText: priceText
            });
        });

        // Deduplicate by title
        var seen = {};
        return items.filter(function (it) {
            var key = (it.title || '').toLowerCase();
            if (!key) return false;
            if (seen[key]) return false;
            seen[key] = true;
            return true;
        }).slice(0, 6);
    }

    function appendSelectableSuggestionCards(wrap, suggestions) {
        if (!suggestions || !suggestions.length) return;
        var cardsWrap = document.createElement('div');
        cardsWrap.className = 'ba-cards ba-cards--selectable';

        suggestions.forEach(function (sug) {
            if (!sug || !sug.title) return;
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'ba-card ba-card--selectable';

            var body = document.createElement('div');
            body.className = 'ba-card__body';

            var top = document.createElement('div');
            top.className = 'ba-card__top';

            var title = document.createElement('div');
            title.className = 'ba-card__title';
            title.textContent = sug.title;

            var meta = document.createElement('div');
            meta.className = 'ba-card__meta';
            if (sug.priceText) {
                var price = document.createElement('span');
                price.className = 'ba-badge ba-badge--price';
                price.textContent = sug.priceText.replace(/\s+/g, ' ').trim();
                meta.appendChild(price);
            }

            top.appendChild(title);
            top.appendChild(meta);
            body.appendChild(top);

            btn.appendChild(body);

            btn.addEventListener('click', function () {
                if (sug.url) {
                    window.open(sug.url, '_blank', 'noopener,noreferrer');
                    return;
                }

                setOpen(true);
                input.value = 'I want to book: ' + sug.title;
                resizeComposer();
                input.focus();
                if (form && typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else if (form) {
                    form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
                }
            });

            cardsWrap.appendChild(btn);
        });

        if (cardsWrap.childNodes.length) {
            wrap.appendChild(cardsWrap);
        }
    }

    /**
     * Mouse: pointer capture + direct scrollLeft updates (smooth, no lost grabs).
     * Touch: native horizontal pan (touch-action: pan-x) — no custom handler to avoid fighting the browser.
     */
    function attachRailPointerScroll(rail) {
        if (!rail || rail.getAttribute('data-ba-rail-scroll') === '1') return;
        rail.setAttribute('data-ba-rail-scroll', '1');

        var capId = null;
        var startX = 0;
        var startSl = 0;
        var dragging = false;
        var suppressClick = false;
        var threshold = 4;

        function cleanup() {
            if (capId !== null) {
                try {
                    rail.releasePointerCapture(capId);
                } catch (err) {}
                capId = null;
            }
            dragging = false;
            rail.classList.remove('ba-cards--rail--dragging');
        }

        rail.addEventListener('pointerdown', function (e) {
            if (e.pointerType !== 'mouse' || e.button !== 0 || !e.isPrimary) return;
            capId = e.pointerId;
            startX = e.clientX;
            startSl = rail.scrollLeft;
            dragging = false;
            suppressClick = false;
            try {
                rail.setPointerCapture(capId);
            } catch (err) {}
        });

        rail.addEventListener('pointermove', function (e) {
            if (e.pointerId !== capId || e.pointerType !== 'mouse') return;
            var dx = e.clientX - startX;
            if (!dragging && Math.abs(dx) > threshold) {
                dragging = true;
                rail.classList.add('ba-cards--rail--dragging');
            }
            if (dragging) {
                e.preventDefault();
                rail.scrollLeft = startSl - dx;
            }
        }, { passive: false });

        rail.addEventListener('pointerup', function (e) {
            if (e.pointerId !== capId) return;
            if (dragging) {
                suppressClick = true;
                rail.setAttribute('data-ba-rail-dragged', '1');
                window.setTimeout(function () {
                    rail.removeAttribute('data-ba-rail-dragged');
                    suppressClick = false;
                }, 200);
            }
            cleanup();
        });

        rail.addEventListener('pointercancel', cleanup);

        rail.addEventListener('click', function (e) {
            if (rail.getAttribute('data-ba-rail-dragged') === '1' || suppressClick) {
                e.preventDefault();
                e.stopPropagation();
            }
        }, true);
    }

    /** Same DOM shape as /guidings `guiding-card` row (image | text), styled for the dark assistant modal. */
    function appendGuidingStyleRailCard(cardsWrap, card) {
        if (!card || !card.url || !card.title) return;

        var slide = document.createElement('div');
        slide.className = 'ba-rail-slide';

        var link = document.createElement('a');
        link.className = 'ba-rail-listing-link d-block text-decoration-none';
        link.href = card.url;
        link.target = '_blank';
        link.rel = 'noopener noreferrer';
        link.setAttribute('role', 'listitem');

        var row = document.createElement('div');
        row.className = 'row g-0 m-0 border shadow-sm rounded guiding-card-wrapper ba-rail-guiding-sync';

        var imgCol = document.createElement('div');
        imgCol.className = 'ba-rail-img-wrap';

        var img = document.createElement('img');
        img.className = 'ba-rail-thumb-img';
        img.alt = card.title;
        img.loading = 'lazy';
        img.decoding = 'async';
        img.src = card.image ? resolveAssetUrl(card.image) : listingThumbPlaceholder;
        img.onerror = function () {
            this.onerror = null;
            this.src = listingThumbPlaceholder;
        };
        imgCol.appendChild(img);

        var desc = document.createElement('div');
        desc.className = 'guiding-item-desc col d-flex flex-column min-w-0 py-2 px-2 px-sm-3';

        var gi = document.createElement('div');
        gi.className = 'guidings-item';

        var titleWrap = document.createElement('div');
        titleWrap.className = 'guidings-item-title';
        var h5 = document.createElement('h5');
        h5.className = 'fw-bolder text-truncate mb-0';
        h5.textContent = card.title;
        titleWrap.appendChild(h5);
        gi.appendChild(titleWrap);
        var typeStr = (card.type || '').toString();

        var snip = truncateSnippet(card.snippet || '', 130);
        if (snip) {
            var p = document.createElement('p');
            p.className = 'ba-rail-snippet small mb-0 mt-1';
            p.textContent = snip;
            gi.appendChild(p);
        }

        var priceTxt = formatMoney(card.price ?? card.min_price ?? null, card.currency || 'EUR');
        if (typeStr || priceTxt) {
            var priceRow = document.createElement('div');
            priceRow.className = 'inclusions-price d-flex align-items-end gap-2 mt-auto pt-2 flex-wrap w-100';
            if (typeStr && priceTxt) {
                priceRow.classList.add('justify-content-between');
            } else if (priceTxt) {
                priceRow.classList.add('justify-content-end');
            }

            if (typeStr) {
                var leftBadges = document.createElement('div');
                leftBadges.className = 'd-flex flex-wrap align-items-center gap-1';
                var b = document.createElement('span');
                b.className = 'badge rounded-pill bg-light text-dark border';
                b.style.fontSize = '0.68rem';
                b.style.fontWeight = '600';
                b.textContent = typeStr;
                leftBadges.appendChild(b);
                priceRow.appendChild(leftBadges);
            }

            if (priceTxt) {
                var rightPrice = document.createElement('div');
                rightPrice.className = 'guiding-item-price flex-shrink-0';
                rightPrice.innerHTML = '<h5 class="mb-0 fw-bold text-end"><span class="p-1 rounded ba-rail-price-pill">' + escapeHtml(priceTxt) + '</span></h5>';
                priceRow.appendChild(rightPrice);
            }

            gi.appendChild(priceRow);
        }

        desc.appendChild(gi);
        row.appendChild(imgCol);
        row.appendChild(desc);
        link.appendChild(row);
        slide.appendChild(link);
        cardsWrap.appendChild(slide);
    }

    function appendBubble(role, text, ui) {
        var wrap = document.createElement('div');
        wrap.className = 'booking-assistant-msg ' + role;
        var bubble = document.createElement('div');
        bubble.className = 'bubble';
        if (role === 'assistant') {
            if (ui && Array.isArray(ui.cards) && ui.cards.length > 0) {
                text = stripAllCardJsonBlocks(String(text || ''));
                var j = String(text || '').search(/\{\s*"(?:content|ui|cards)"/);
                if (j > 0) {
                    text = String(text).slice(0, j).trim();
                }
                if (!String(text || '').trim()) {
                    text = listingsIntro;
                }
            }
            bubble.innerHTML = linkifyAndHighlight(text);
        } else {
            bubble.textContent = text;
        }
        wrap.appendChild(bubble);

        // Cards (assistant only)
        if (role === 'assistant' && ui && Array.isArray(ui.cards) && ui.cards.length) {
            var cardsWrap = document.createElement('div');
            cardsWrap.className = 'ba-cards ba-cards--rail';
            cardsWrap.setAttribute('role', 'list');

            ui.cards.slice(0, 8).forEach(function (card) {
                appendGuidingStyleRailCard(cardsWrap, card);
            });

            if (cardsWrap.childNodes.length) {
                wrap.appendChild(cardsWrap);
                attachRailPointerScroll(cardsWrap);
            }
        } else if (role === 'assistant') {
            var t0 = (text || '').trim();
            if (!(t0.charAt(0) === '{' && (t0.indexOf('"ui"') !== -1 || t0.indexOf('"cards"') !== -1))) {
                appendSelectableSuggestionCards(wrap, parseSuggestionLines(text));
            }
        }

        messagesEl.appendChild(wrap);
        scrollToBottom();
    }

    function appendTyping() {
        var wrap = document.createElement('div');
        wrap.className = 'booking-assistant-msg assistant';
        var bubble = document.createElement('div');
        bubble.className = 'bubble';
        var typing = document.createElement('span');
        typing.className = 'booking-assistant-typing';
        typing.setAttribute('aria-label', @json(__('booking-assistant.widget_thinking')));
        typing.innerHTML = '<i></i><i></i><i></i>';
        bubble.appendChild(typing);
        wrap.appendChild(bubble);
        messagesEl.appendChild(wrap);
        scrollToBottom();
        return wrap;
    }

    function updateShellState() {
        if (!modalRoot || !messagesEl) return;
        var hasChat = !!(messagesEl.querySelector('.booking-assistant-msg')) || (history && history.length > 0);
        modalRoot.classList.toggle('ba-state--chat', hasChat);
        modalRoot.classList.toggle('ba-state--splash', !hasChat);
    }

    function setOpen(open) {
        if (!modalRoot) return;
        modalRoot.classList.toggle('is-open', open);
        modalRoot.setAttribute('aria-hidden', open ? 'false' : 'true');
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        try {
            document.documentElement.classList.toggle('booking-assistant-modal-open', open);
        } catch (e) {}
        if (open) {
            setTimeout(function () { input && input.focus(); }, 0);
            setTimeout(scrollToBottom, 0);
        } else if (toggle) {
            setTimeout(function () { toggle.focus(); }, 0);
        }
        updateTeaserVisibility();
        updateShellState();
    }

    function isModalOpen() {
        return !!(modalRoot && modalRoot.classList.contains('is-open'));
    }

    function resizeComposer() {
        if (!input || input.tagName !== 'TEXTAREA') return;
        input.style.height = 'auto';
        var max = 180;
        input.style.height = Math.min(input.scrollHeight, max) + 'px';
    }

    if (input && input.tagName === 'TEXTAREA') {
        input.addEventListener('input', resizeComposer);
        input.addEventListener('keydown', function (e) {
            if (e.key !== 'Enter' || e.shiftKey) return;
            e.preventDefault();
            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
            } else {
                form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
            }
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        if (!isModalOpen()) return;
        setOpen(false);
        saveThread();
    });

    function isThreadEmpty() {
        return !history || !history.length;
    }

    var spotlightDismissKey = 'booking-assistant:spotlight-dismissed';
    function initSpotlight() {
        if (!spotlightEl) return;
        var dismissed = false;
        try { dismissed = sessionStorage.getItem(spotlightDismissKey) === '1'; } catch (e) { dismissed = false; }
        if (dismissed) {
            spotlightEl.classList.add('is-dismissed');
        }
        if (spotlightDismissBtn) {
            spotlightDismissBtn.addEventListener('click', function () {
                spotlightEl.classList.add('is-dismissed');
                try { sessionStorage.setItem(spotlightDismissKey, '1'); } catch (e) {}
            });
        }
    }
    initSpotlight();

    function updateTeaserVisibility() {
        if (!teaserBtn) return;
        var shouldShow = isThreadEmpty() && !isModalOpen();
        teaserBtn.classList.toggle('is-visible', shouldShow);
        teaserBtn.setAttribute('aria-hidden', shouldShow ? 'false' : 'true');
        teaserBtn.tabIndex = shouldShow ? 0 : -1;
    }

    toggle.addEventListener('click', function () {
        setOpen(!isModalOpen());
        saveThread();
    });
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            clearThread();
            saveThread();
            updateTeaserVisibility();
            updateShellState();
        });
    }
    closeBtn.addEventListener('click', function () {
        setOpen(false);
        saveThread();
    });
    if (teaserBtn) {
        teaserBtn.addEventListener('click', function () {
            setOpen(true);
            input && input.focus();
        });
        teaserBtn.addEventListener('mouseenter', function () {
            teaserBtn.classList.add('is-peek');
        });
        teaserBtn.addEventListener('mouseleave', function () {
            teaserBtn.classList.remove('is-peek');
        });
    }

    var suggestions = @json($bookingAssistantWelcomeSuggestions);
    function renderSuggestions() {
        if (!suggestionsEl) return;
        suggestionsEl.innerHTML = '';
        suggestions.forEach(function (label) {
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'booking-assistant-chip';
            b.textContent = label;
            b.addEventListener('click', function () {
                setOpen(true);
                input.value = label;
                resizeComposer();
                input.focus();
            });
            suggestionsEl.appendChild(b);
        });
    }
    renderSuggestions();
    restoreThread();
    updateTeaserVisibility();
    updateShellState();

    window.addEventListener('beforeunload', function () {
        saveThread();
    });

    function renderQuickReplies(quickReplies) {
        if (!suggestionsEl) return;
        if (!Array.isArray(quickReplies) || !quickReplies.length) return;
        suggestionsEl.innerHTML = '';
        suggestionsEl.style.display = '';
        quickReplies.slice(0, 8).forEach(function (label) {
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'booking-assistant-chip';
            b.textContent = label;
            b.addEventListener('click', function () {
                setOpen(true);
                input.value = label;
                resizeComposer();
                input.focus();
            });
            suggestionsEl.appendChild(b);
        });
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var text = (input.value || '').trim();
        if (!text) return;
        if (text === '/clear') {
            input.value = '';
            resizeComposer();
            clearThread();
            return;
        }
        input.value = '';
        resizeComposer();
        // Hide initial suggestions once the conversation starts
        if (suggestionsEl) {
            suggestionsEl.style.display = 'none';
        }
        if (teaserBtn) {
            teaserBtn.classList.remove('is-visible');
        }
        var payloadMessages = history.concat([{ role: 'user', content: text }]);
        appendBubble('user', text);
        updateShellState();
        var thinking = appendTyping();
        sendBtn.disabled = true;

        fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                messages: payloadMessages,
                page_context: pageContext
            })
        }).then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
        .then(function (pack) {
            messagesEl.removeChild(thinking);
            if (!pack.ok || !pack.data || !pack.data.message) {
                appendBubble('assistant', @json(__('booking-assistant.widget_error')));
                return;
            }
            var norm = normalizeAssistantPayload(pack.data.message.content, pack.data.ui || null);
            if (!norm.content.trim() && !(norm.ui && norm.ui.cards && norm.ui.cards.length)) {
                appendBubble('assistant', @json(__('booking-assistant.widget_error')));
                return;
            }
            history.push({ role: 'user', content: text });
            history.push({ role: 'assistant', content: norm.content, ui: norm.ui });
            if (history.length > 24) {
                history = history.slice(-24);
            }
            appendBubble('assistant', norm.content, norm.ui);
            if (norm.ui && norm.ui.quick_replies) {
                renderQuickReplies(norm.ui.quick_replies);
            }
            saveThread();
        }).catch(function () {
            messagesEl.removeChild(thinking);
            appendBubble('assistant', @json(__('booking-assistant.widget_error')));
        }).finally(function () {
            sendBtn.disabled = false;
            updateShellState();
        });
    });

    window.BookingAssistant = {
        open: function () { setOpen(true); saveThread(); },
        close: function () { setOpen(false); saveThread(); },
        toggle: function () { setOpen(!isModalOpen()); saveThread(); },
        isOpen: isModalOpen
    };

    document.addEventListener('booking-assistant:open', function () {
        setOpen(true);
        saveThread();
    });
})();
</script>