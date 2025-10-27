@once
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endonce

<script>
document.addEventListener('alpine:init', () => {
    // Accommodation Card Alpine.js Component
    Alpine.data('accommodationCard', (accommodation) => ({
        accommodation,
        images: [],
        currentImageIndex: 0,

        init() {
            // Build images array from thumbnail and gallery
            this.images = [
                accommodation.thumbnail_path,
                ...(accommodation.gallery_images || [])
            ].filter(Boolean);
        },

        get currentImage() {
            return this.images[this.currentImageIndex] || this.images[0] || 'https://images.unsplash.com/photo-1519710164239-da123dc03ef4?q=80&w=1600&auto=format&fit=crop';
        },

        nextImage() {
            if (this.images.length > 1) {
                this.currentImageIndex = (this.currentImageIndex + 1) % this.images.length;
            }
        },

        prevImage() {
            if (this.images.length > 1) {
                this.currentImageIndex = (this.currentImageIndex - 1 + this.images.length) % this.images.length;
            }
        },

        fmt(value, currency = 'EUR') {
            try {
                return new Intl.NumberFormat('en-US', { style: 'currency', currency }).format(value ?? 0);
            } catch (error) {
                const amount = typeof value === 'number' ? value.toFixed(2) : '0.00';
                return `${amount} ${currency}`;
            }
        },

        getBedSummary() {
            const bed = this.accommodation.bed_config || {};
            const parts = [];
            if (bed.single) parts.push(`${bed.single}× Single`);
            if (bed.double) parts.push(`${bed.double}× Double`);
            if (bed.sofabed) parts.push(`${bed.sofabed}× Sofa`);
            if (bed.bunk) parts.push(`${bed.bunk}× Bunk`);
            if (bed.child) parts.push(`${bed.child}× Child`);
            if (bed.folding) parts.push(`${bed.folding}× Folding`);
            return parts.join(' · ') || '—';
        },

        getKitchenList() {
            const k = this.accommodation.kitchen || {};
            const list = [];
            if (k.refrigerator_freezer || k.freezer_compartment) list.push('Fridge/Freezer');
            if (k.oven) list.push('Oven');
            if (k.stove) list.push('Stove');
            if (k.microwave) list.push('Microwave');
            if (k.dishwasher) list.push('Dishwasher');
            if (k.coffee_machine) list.push(`Coffee${typeof k.coffee_machine === 'string' ? ' (' + k.coffee_machine + ')' : ''}`);
            if (k.kettle) list.push('Kettle');
            if (k.toaster) list.push('Toaster');
            if (k.blender) list.push('Blender');
            if (k.cutlery) list.push('Cutlery');
            if (k.wine_glasses) list.push('Wine glasses');
            if (k.pans_pots) list.push('Pans & Pots');
            if (k.baking_equipment) list.push('Baking utensils');
            if (k.dishwashing_items) list.push('Dish soap/Sponge');
            if (k.sink) list.push('Sink');
            if (k.basics) list.push('Basics (Oil/Spices)');
            return list;
        },

        getBathList() {
            const b = this.accommodation.bathroom_laundry || {};
            const list = [];
            if (b.shower != null) list.push(`Showers ${b.shower}`);
            if (b.toilet != null) list.push(`WC ${b.toilet}`);
            if (b.washbasin != null) list.push(`Washbasin ${b.washbasin}`);
            if (b.separate_wc_bath) list.push('Separate WC/Bath');
            if (b.washing_machine) list.push('Washing machine');
            if (b.dryer) list.push('Dryer');
            if (b.iron_board) list.push('Iron/Board');
            if (b.drying_rack) list.push('Drying rack');
            return list;
        },

        getPolicyList() {
            const p = this.accommodation.policies || {};
            const list = [];
            if (p.pets_allowed != null) list.push(`Pets ${p.pets_allowed ? 'allowed' : 'forbidden'}`);
            if (p.smoking_allowed != null) list.push(`Smoking ${p.smoking_allowed ? 'allowed' : 'forbidden'}`);
            if (p.children_allowed != null) list.push(`Children ${p.children_allowed ? 'allowed' : '—'}`);
            if (p.accessible != null) list.push(`Accessible ${p.accessible ? 'yes' : 'no'}`);
            if (p.self_checkin != null) list.push(`Self check-in ${p.self_checkin ? 'yes' : 'no'}`);
            if (p.only_registered_guests != null) list.push(`Only registered guests ${p.only_registered_guests ? 'yes' : 'no'}`);
            if (p.deposit_required != null) list.push(`Deposit ${p.deposit_required ? 'required' : 'no'}`);
            if (p.energy_included != null) list.push(`Energy incl. ${p.energy_included ? 'yes' : 'no'}`);
            if (p.water_included != null) list.push(`Water incl. ${p.water_included ? 'yes' : 'no'}`);
            if (p.quiet_hours) list.push(`Quiet hours ${p.quiet_hours}`);
            if (p.waste_rules) list.push(`Waste/Recycling: ${p.waste_rules}`);
            return list;
        },

        getConditions() {
            const conditions = [];
            if (this.accommodation.changeover_day) conditions.push(`Changeover day ${this.accommodation.changeover_day}`);
            if (this.accommodation.minimum_stay_nights) conditions.push(`${this.accommodation.minimum_stay_nights} nights min.`);
            return conditions.join(' · ') || '—';
        }
    }));

    // Guiding Card Alpine.js Component
    Alpine.data('guidingCard', (guiding) => ({
        guiding,
        images: [],
        currentImageIndex: 0,

        init() {
            // Build images array from thumbnail and gallery
            this.images = [
                guiding.thumbnail_path,
                ...(guiding.gallery_images || [])
            ].filter(Boolean);
        },

        get currentImage() {
            return this.images[this.currentImageIndex] || this.images[0] || 'https://images.unsplash.com/photo-1474843148229-3163319fcc00?q=80&w=1600&auto=format&fit=crop';
        },

        nextImage() {
            if (this.images.length > 1) {
                this.currentImageIndex = (this.currentImageIndex + 1) % this.images.length;
            }
        },

        prevImage() {
            if (this.images.length > 1) {
                this.currentImageIndex = (this.currentImageIndex - 1 + this.images.length) % this.images.length;
            }
        },

        fmt(value, currency = 'EUR') {
            try {
                return new Intl.NumberFormat('en-US', { style: 'currency', currency }).format(value ?? 0);
            } catch (error) {
                const amount = typeof value === 'number' ? value.toFixed(2) : '0.00';
                return `${amount} ${currency}`;
            }
        }
    }));

    // Boat Card Alpine.js Component
    Alpine.data('boatCard', (boat) => ({
        boat,
        images: [],
        currentImageIndex: 0,

        init() {
            // Build images array from thumbnail and gallery
            this.images = [
                boat.thumbnail_path,
                ...(boat.gallery_images || [])
            ].filter(Boolean);
        },

        get currentImage() {
            return this.images[this.currentImageIndex] || this.images[0] || 'https://images.unsplash.com/photo-1520440229-84f3865cf003?q=80&w=1600&auto=format&fit=crop';
        },

        nextImage() {
            if (this.images.length > 1) {
                this.currentImageIndex = (this.currentImageIndex + 1) % this.images.length;
            }
        },

        prevImage() {
            if (this.images.length > 1) {
                this.currentImageIndex = (this.currentImageIndex - 1 + this.images.length) % this.images.length;
            }
        },

        fmt(value, currency = 'EUR') {
            try {
                return new Intl.NumberFormat('en-US', { style: 'currency', currency }).format(value ?? 0);
            } catch (error) {
                const amount = typeof value === 'number' ? value.toFixed(2) : '0.00';
                return `${amount} ${currency}`;
            }
        }
    }));
});
</script>

