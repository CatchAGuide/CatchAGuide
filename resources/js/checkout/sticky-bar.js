/**
 * Mobile total + reserve bar: shown once the product card has scrolled away and hidden again
 * while the in-page call to action is on screen. Desktop keeps it hidden via CSS, and the
 * observers only report while the mobile media query matches.
 */
const CTA_REVEAL = 60;

export class StickyBarWatcher {
    /**
     * @param {{ anchor: Element, cta: Element, media: string, onChange: (visible: boolean) => void }} options
     */
    constructor({ anchor, cta, media, onChange }) {
        this.anchor = anchor;
        this.cta = cta;
        this.onChange = onChange;
        this.mediaQuery = window.matchMedia(media);
        this.pastAnchor = false;
        this.ctaVisible = false;
        this.observers = [];
        this.handleMedia = () => this.emit();
    }

    start() {
        if (!this.anchor || !this.cta || !('IntersectionObserver' in window)) {
            return this;
        }

        this.observers = [
            new IntersectionObserver(([entry]) => {
                this.pastAnchor = !entry.isIntersecting && entry.boundingClientRect.top < 0;
                this.emit();
            }),
            // The CTA only counts as visible once it is CTA_REVEAL px into the viewport, so a
            // sliver of it at the bottom edge doesn't hide the bar (as in the mobile design).
            new IntersectionObserver(([entry]) => {
                this.ctaVisible = entry.isIntersecting;
                this.emit();
            }, { rootMargin: `0px 0px -${CTA_REVEAL}px 0px` }),
        ];

        this.observers[0].observe(this.anchor);
        this.observers[1].observe(this.cta);
        this.mediaQuery.addEventListener('change', this.handleMedia);

        return this;
    }

    stop() {
        this.observers.forEach((observer) => observer.disconnect());
        this.mediaQuery.removeEventListener('change', this.handleMedia);
    }

    emit() {
        this.onChange(this.mediaQuery.matches && this.pastAnchor && !this.ctaVisible);
    }
}
