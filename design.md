
name: Scanproof Design System
version: 1.1.0
author: Stitch
description: A high-efficiency facility management design system focused on "Speed + Clarity" for both administrative desktop workflows and mobile-first operational tasks.

Visual Identity

Color Palette

The palette is anchored by a high-contrast primary blue, supported by a professional suite of neutral grays for structural elements and semantic colors for operational status.

Brand Colors





Primary Blue: #0B5FFF (Main CTAs, Active States, Brand Marks)



Secondary: Neutral Grays for borders and secondary text.

Semantic Colors





Success: Emerald Green (Completed tasks, Linked status)



Warning: Amber/Orange (Pending items, Flagged reports)



Error/Overdue: Crimson Red (Overdue tasks, Critical issues)



Information: Light Blue (In-progress indicators)

Typography

The system uses Inter as the primary typeface to ensure maximum legibility across high-density data tables and small mobile screens.





Display: 36px/Bold (Main page headers)



Headline: 20px/SemiBold (Section titles, Modal headers)



Body: 16px/Regular (Standard text, Input labels)



Label: 12-14px/Medium (Pills, Status badges, Meta-data)

UI Geometry & Spacing





Corner Radius: rounded-xl (12px) for cards, buttons, and containers.



Elevation: Flat design with subtle shadow-sm on floating elements (modals, cards).



Grid: Standard 12-column layout for desktop; single-column fluid layout for mobile.



Page Architecture & flows

1. Authentication Flow





Login ({{DATA:SCREEN:SCREEN_4}}): Minimal, centered layout. Focused on speed.



Registration ({{DATA:SCREEN:SCREEN_25}}): Multi-step flow including email verification and organization setup.



Forgot Password ({{DATA:SCREEN:SCREEN_26}}): Streamlined recovery process matching the login aesthetic.

2. Administrative Suite (Desktop)





Dashboard ({{DATA:SCREEN:SCREEN_15}}): High-level overview of facility health, response times, and compliance metrics.



Tasks List ({{DATA:SCREEN:SCREEN_11}}): Density-optimized list view with color-coded status pills and location tracking.



Task Details ({{DATA:SCREEN:SCREEN_23}}): Deep dive into specific task proof, timestamps, and resolution evidence.



Reports ({{DATA:SCREEN:SCREEN_2}}): Data-heavy table views for daily logs, per-location analysis, and issue tracking.



Location Management ({{DATA:SCREEN:SCREEN_13}}): Tools for setting up physical spaces and assigning digital barcodes.

3. Operational & Public Flows (Mobile-First)





Mobile Homepage ({{DATA:SCREEN:SCREEN_19}}): Triage point for field technicians and visitors.



QR Scanner ({{DATA:SCREEN:SCREEN_17}}): Immersive viewfinder for instant asset identification.



Scan Success ({{DATA:SCREEN:SCREEN_21}}): Contextual overlay providing immediate task details post-scan.



Visitor Reporting ({{DATA:SCREEN:SCREEN_8}}): Public-facing, zero-friction form for anonymous issue reporting.



Component Specifications

1. Global Navigation





Side Nav (Desktop): w-72, bg-surface-container-low. Active state indicated by Primary Blue text and right border.



Top Bar (Desktop): h-20, sticky. Includes global search, notifications, and profile.



Bottom Nav (Mobile): Fixed bottom bar for quick switching between Dashboard, Tasks, and Scanning.

2. Data Tables





Header: Light gray background, uppercase bold labels.



Rows: Alternating backgrounds with hover:bg-surface-container-low.



Status Pills: rounded-full with semantic backgrounds (Success/Warning/Error).

3. Forms & Modals





Create Task Modal ({{DATA:SCREEN:SCREEN_28}}): Centered overlay with clear section dividers and primary blue CTAs.



Input Style: Outlined fields with 12px rounding. Focus state uses 2px Primary Blue border.



Technical Design Principles





State Preservation: UI must clearly reflect current status (Pending/In-Progress/Completed) using established semantic tokens.



Mobile Continuity: Field operations must feel identical to administrative views in branding, even with reduced density.



Action Priority: Primary actions (Scan, Submit, Create) should always use the Primary Blue rounded-xl button style.