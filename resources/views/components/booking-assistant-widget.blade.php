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
@endphp
<div id="booking-assistant-root" data-endpoint="{{ url('/assistant/chat') }}" data-csrf="{{ csrf_token() }}" data-page-context="{{ e($ctx) }}">
    <button type="button" class="booking-assistant-fab" id="booking-assistant-toggle" aria-expanded="false" aria-controls="booking-assistant-panel" title="{{ __('booking-assistant.widget_open') }}">
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
    <div class="booking-assistant-panel" id="booking-assistant-panel" role="dialog" aria-label="{{ __('booking-assistant.widget_title') }}">
        <div class="booking-assistant-head">
            <span>{{ __('booking-assistant.widget_title') }}</span>
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
        </div>
        <div class="booking-assistant-body" id="booking-assistant-messages">
            <div class="booking-assistant-suggestions" id="booking-assistant-suggestions" aria-label="Suggestions"></div>
        </div>
        <form class="booking-assistant-foot" id="booking-assistant-form">
            <input type="text" id="booking-assistant-input" maxlength="2000" placeholder="{{ __('booking-assistant.widget_placeholder') }}" autocomplete="off" />
            <button type="submit" class="send" id="booking-assistant-send">{{ __('booking-assistant.widget_send') }}</button>
        </form>
    </div>
</div>
<script>
(function () {
    var root = document.getElementById('booking-assistant-root');
    if (!root) return;
    var endpoint = root.getAttribute('data-endpoint');
    var csrf = root.getAttribute('data-csrf');
    var pageContext = root.getAttribute('data-page-context') || '';
    var panel = document.getElementById('booking-assistant-panel');
    var toggle = document.getElementById('booking-assistant-toggle');
    var clearBtn = document.getElementById('booking-assistant-clear');
    var closeBtn = document.getElementById('booking-assistant-close');
    var messagesEl = document.getElementById('booking-assistant-messages');
    var suggestionsEl = document.getElementById('booking-assistant-suggestions');
    var form = document.getElementById('booking-assistant-form');
    var input = document.getElementById('booking-assistant-input');
    var sendBtn = document.getElementById('booking-assistant-send');
    var teaserBtn = document.getElementById('booking-assistant-teaser');
    var history = [];

    // sessionStorage is per-tab; localStorage is shared across tabs.
    // We store in both so a new tab can restore the same thread.
    var sessionKey = 'booking-assistant:thread:session:' + (pageContext || 'global');
    var sharedKey = 'booking-assistant:thread:shared';
    var threadTtlMs = 1000 * 60 * 60 * 4; // 4 hours
    
    function scrollToBottom() {
        if (!messagesEl) return;
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function saveThread() {
        var payload = {
            v: 2,
            savedAt: Date.now(),
            pageContext: pageContext,
            isOpen: !!(panel && panel.classList && panel.classList.contains('is-open')),
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
            if (!m || !m.role || !m.content) return;
            appendBubble(m.role, m.content, m.ui || null);
        });

        // If there's an active session/thread, open the panel by default (especially for new tabs).
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
        setTimeout(scrollToBottom, 0);
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

    function appendBubble(role, text, ui) {
        var wrap = document.createElement('div');
        wrap.className = 'booking-assistant-msg ' + role;
        var bubble = document.createElement('div');
        bubble.className = 'bubble';
        if (role === 'assistant') {
            bubble.innerHTML = linkifyAndHighlight(text);
        } else {
            bubble.textContent = text;
        }
        wrap.appendChild(bubble);

        // Cards (assistant only)
        if (role === 'assistant' && ui && Array.isArray(ui.cards) && ui.cards.length) {
            var cardsWrap = document.createElement('div');
            cardsWrap.className = 'ba-cards';

            ui.cards.slice(0, 6).forEach(function (card) {
                if (!card || !card.url || !card.title) return;

                var a = document.createElement('a');
                a.className = 'ba-card';
                a.href = card.url;
                a.target = '_blank';
                a.rel = 'noopener noreferrer';

                var img = document.createElement('div');
                img.className = 'ba-card__img';
                if (card.image) {
                    img.style.backgroundImage = 'url(' + card.image + ')';
                } else {
                    img.setAttribute('data-fallback', '1');
                }

                var body = document.createElement('div');
                body.className = 'ba-card__body';

                var top = document.createElement('div');
                top.className = 'ba-card__top';

                var title = document.createElement('div');
                title.className = 'ba-card__title';
                title.textContent = card.title;

                var meta = document.createElement('div');
                meta.className = 'ba-card__meta';
                var t = (card.type || '').toString();
                if (t) {
                    var badge = document.createElement('span');
                    badge.className = 'ba-badge';
                    badge.textContent = t;
                    meta.appendChild(badge);
                }
                var priceTxt = formatMoney(card.price ?? card.min_price ?? null, card.currency || 'EUR');
                if (priceTxt) {
                    var price = document.createElement('span');
                    price.className = 'ba-badge ba-badge--price';
                    price.textContent = priceTxt;
                    meta.appendChild(price);
                }

                var snippet = document.createElement('div');
                snippet.className = 'ba-card__snippet';
                snippet.textContent = (card.snippet || '').toString();

                top.appendChild(title);
                top.appendChild(meta);
                body.appendChild(top);
                if (snippet.textContent) body.appendChild(snippet);

                a.appendChild(img);
                a.appendChild(body);
                cardsWrap.appendChild(a);
            });

            if (cardsWrap.childNodes.length) {
                wrap.appendChild(cardsWrap);
            }
        } else if (role === 'assistant') {
            // Fallback: turn numbered/bulleted suggestions into selectable cards
            appendSelectableSuggestionCards(wrap, parseSuggestionLines(text));
        }

        messagesEl.appendChild(wrap);
        messagesEl.scrollTop = messagesEl.scrollHeight;
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
        messagesEl.scrollTop = messagesEl.scrollHeight;
        return wrap;
    }

    function setOpen(open) {
        panel.classList.toggle('is-open', open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (open) {
            setTimeout(function () { input && input.focus(); }, 0);
            setTimeout(scrollToBottom, 0);
        }
        updateTeaserVisibility();
        maybeShowIntro(open);
    }

    function isThreadEmpty() {
        return !history || !history.length;
    }

    var introKey = 'booking-assistant:intro-shown:' + (pageContext || 'global');
    function introExistsInDom() {
        if (!messagesEl) return false;
        return !!messagesEl.querySelector('.booking-assistant-msg.ba-intro');
    }
    function getIntroShown() {
        try { return sessionStorage.getItem(introKey) === '1'; } catch (e) { return false; }
    }
    function setIntroShown() {
        try { sessionStorage.setItem(introKey, '1'); } catch (e) {}
    }
    function clearIntroShown() {
        try { sessionStorage.removeItem(introKey); } catch (e) {}
    }
    function maybeShowIntro(open) {
        if (!open) return;
        if (!messagesEl) return;
        if (!isThreadEmpty()) return;
        if (introExistsInDom()) return;
        if (getIntroShown()) return;
        setIntroShown();

        var wrap = document.createElement('div');
        wrap.className = 'booking-assistant-msg assistant ba-intro';
        var bubble = document.createElement('div');
        bubble.className = 'bubble';
        bubble.innerHTML = linkifyAndHighlight(@json(__('booking-assistant.widget_intro')));
        wrap.appendChild(bubble);

        // Place intro right under suggestions (so chips remain visible)
        if (suggestionsEl && suggestionsEl.parentNode === messagesEl) {
            messagesEl.insertBefore(wrap, suggestionsEl.nextSibling);
        } else {
            messagesEl.appendChild(wrap);
        }
        setTimeout(scrollToBottom, 0);
    }

    function updateTeaserVisibility() {
        if (!teaserBtn) return;
        var shouldShow = isThreadEmpty() && !(panel && panel.classList && panel.classList.contains('is-open'));
        teaserBtn.classList.toggle('is-visible', shouldShow);
        teaserBtn.setAttribute('aria-hidden', shouldShow ? 'false' : 'true');
        teaserBtn.tabIndex = shouldShow ? 0 : -1;
    }

    toggle.addEventListener('click', function () {
        setOpen(!panel.classList.contains('is-open'));
        saveThread();
    });
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            clearThread();
            clearIntroShown();
            saveThread();
            updateTeaserVisibility();
            maybeShowIntro(panel && panel.classList && panel.classList.contains('is-open'));
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

    var suggestions = @json([
        'I want to book a trip',
        'Show me popular trips',
        'I have a question about my booking'
    ]);
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
                input.focus();
            });
            suggestionsEl.appendChild(b);
        });
    }
    renderSuggestions();
    restoreThread();
    updateTeaserVisibility();
    // If thread is empty but widget is open on load (edge cases), still show intro once.
    maybeShowIntro(panel && panel.classList && panel.classList.contains('is-open'));

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
            clearThread();
            return;
        }
        input.value = '';
        // Hide initial suggestions once the conversation starts
        if (suggestionsEl) {
            suggestionsEl.style.display = 'none';
        }
        if (teaserBtn) {
            teaserBtn.classList.remove('is-visible');
        }
        var payloadMessages = history.concat([{ role: 'user', content: text }]);
        appendBubble('user', text);
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
            if (!pack.ok || !pack.data || !pack.data.message || !pack.data.message.content) {
                appendBubble('assistant', @json(__('booking-assistant.widget_error')));
                return;
            }
            var reply = pack.data.message.content;
            var ui = pack.data.ui || null;
            history.push({ role: 'user', content: text });
            history.push({ role: 'assistant', content: reply, ui: ui });
            if (history.length > 24) {
                history = history.slice(-24);
            }
            appendBubble('assistant', reply, ui);
            if (ui && ui.quick_replies) {
                renderQuickReplies(ui.quick_replies);
            }
            saveThread();
        }).catch(function () {
            messagesEl.removeChild(thinking);
            appendBubble('assistant', @json(__('booking-assistant.widget_error')));
        }).finally(function () {
            sendBtn.disabled = false;
        });
    });
})();
</script>