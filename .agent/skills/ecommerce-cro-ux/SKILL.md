---
name: ecommerce-cro-ux
description: >-
  Specialized skill for E-Commerce User Experience (UX), Conversion Rate Optimization (CRO),
  micro-interactions, interactive hover feedback, responsive design, and accessible form controls.
---

# E-Commerce UX & CRO Skill

## 1. Micro-Interactions & Hover States
- **Product Cards:** Smooth elevation on hover (`transform: translateY(-4px)`, `box-shadow: 0 12px 24px -8px rgba(0, 0, 0, 0.08)`).
- **Interactive Buttons:** Clear hover state transition (`transform: translateY(-1px)`, slight brightness increase or brand shadow). Minimum tap target size of 44x44px.
- **Image Hover Effects:** Subtle scale transition (`transform: scale(1.04)`) with `overflow: hidden` on parent containers to avoid layout shifts.
- **Feedback Indicators:** Add-to-cart spinner, checkmark, and automatic drawer slide-in upon completion.

## 2. Form Inputs & Textboxes (Footer & Checkout)
- **Newsletter Subscription:**
  - High-visibility email input with clear placeholder (`Tu correo electrónico...`).
  - Accessible label/aria-label for screen readers.
  - Inline action button with brand primary color (`#ffd60a`) and crisp icon/text (`Suscribirme`).
  - Validation styling (`:focus` state with brand focus ring `outline: 2px solid #ffd60a; outline-offset: 2px;`).
- **Footer Architecture:**
  - Column 1: Brand identity, logo, value proposition, social links.
  - Column 2: Navigation & Quick Links with interactive hover underline/color change.
  - Column 3: Customer Service, FAQs, shipping policies.
  - Column 4: Newsletter capture textbox + trust badges (payment methods, security icons).
  - Bottom Bar: Copyright notice, legal links, seamless alignment.

## 3. Responsive & Mobile-First Best Practices
- Sticky bottom action or easy-to-reach cart badge on mobile.
- Full viewport height drawers (`height: 100dvh`) with native smooth touch scrolling.
- Prevent accidental zoom on mobile inputs by using `font-size: 16px` minimum.
