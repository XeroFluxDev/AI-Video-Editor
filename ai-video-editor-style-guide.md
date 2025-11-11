# AI Video Editor - Style Guide & Design System

## Overview

This style guide defines the visual design language, UI components, and interaction patterns for the AI-powered video editing tool. The design prioritizes a professional, modern aesthetic with a dark-first approach and seamless theme switching capability.

---

## Design Philosophy

### Core Principles
- **Dark First**: Primary interface in dark mode, optimized for extended editing sessions
- **Professional**: Clean, polished interface suitable for content creators
- **Minimalist**: Focus on functionality, minimal visual noise
- **Responsive**: Fluid design that adapts to all screen sizes
- **Accessible**: WCAG 2.1 AA compliant with proper contrast ratios
- **Performance**: Smooth animations and instant feedback

### Visual Language
- Modern, flat design with subtle depth
- Consistent spacing and alignment
- Clear visual hierarchy
- Purposeful use of color
- Smooth micro-interactions

---

## Color System

### Dark Theme (Default)

**Background Colors**
```css
--bg-primary: #0f0f0f;        /* Main background */
--bg-secondary: #1a1a1a;      /* Panels, cards */
--bg-tertiary: #242424;       /* Elevated elements */
--bg-hover: #2a2a2a;          /* Hover states */
--bg-active: #303030;         /* Active/selected states */
```

**Text Colors**
```css
--text-primary: #f5f5f5;      /* Primary text */
--text-secondary: #a0a0a0;    /* Secondary text */
--text-tertiary: #6b6b6b;     /* Disabled, hints */
--text-inverse: #0f0f0f;      /* Text on light backgrounds */
```

**Border Colors**
```css
--border-primary: #333333;    /* Default borders */
--border-secondary: #404040;  /* Subtle borders */
--border-focus: #4a9eff;      /* Focus states */
```

**Accent Colors**
```css
--accent-primary: #4a9eff;    /* Primary actions, links */
--accent-hover: #6ab0ff;      /* Hover state */
--accent-active: #3a8eef;     /* Active state */

--accent-success: #10b981;    /* Success states */
--accent-warning: #f59e0b;    /* Warning states */
--accent-error: #ef4444;      /* Error states */
--accent-info: #3b82f6;       /* Info states */
```

**Video Editor Specific**
```css
--timeline-bg: #1a1a1a;       /* Timeline background */
--timeline-track: #242424;    /* Track background */
--timeline-clip: #4a9eff;     /* Video clip */
--timeline-audio: #10b981;    /* Audio waveform */
--timeline-subtitle: #f59e0b; /* Subtitle track */
--timeline-marker: #ef4444;   /* Playhead, markers */
--timeline-selection: rgba(74, 158, 255, 0.2); /* Selection overlay */
```

### Light Theme

**Background Colors**
```css
--bg-primary: #ffffff;        /* Main background */
--bg-secondary: #f5f5f5;      /* Panels, cards */
--bg-tertiary: #e5e5e5;       /* Elevated elements */
--bg-hover: #f0f0f0;          /* Hover states */
--bg-active: #e0e0e0;         /* Active/selected states */
```

**Text Colors**
```css
--text-primary: #0f0f0f;      /* Primary text */
--text-secondary: #404040;    /* Secondary text */
--text-tertiary: #6b6b6b;     /* Disabled, hints */
--text-inverse: #ffffff;      /* Text on dark backgrounds */
```

**Border Colors**
```css
--border-primary: #d4d4d4;    /* Default borders */
--border-secondary: #e5e5e5;  /* Subtle borders */
--border-focus: #3b82f6;      /* Focus states */
```

**Accent Colors** (Same as dark theme, adjusted for visibility)
```css
--accent-primary: #3b82f6;    /* Slightly darker for contrast */
--accent-hover: #2563eb;
--accent-active: #1d4ed8;
/* Success, warning, error remain the same */
```

### Tailwind CSS Configuration

```javascript
// tailwind.config.js
module.exports = {
  darkMode: 'class', // Enable class-based dark mode
  theme: {
    extend: {
      colors: {
        dark: {
          bg: {
            primary: '#0f0f0f',
            secondary: '#1a1a1a',
            tertiary: '#242424',
            hover: '#2a2a2a',
            active: '#303030',
          },
          text: {
            primary: '#f5f5f5',
            secondary: '#a0a0a0',
            tertiary: '#6b6b6b',
          },
          border: {
            primary: '#333333',
            secondary: '#404040',
          }
        },
        accent: {
          primary: '#4a9eff',
          hover: '#6ab0ff',
          active: '#3a8eef',
          success: '#10b981',
          warning: '#f59e0b',
          error: '#ef4444',
          info: '#3b82f6',
        },
        timeline: {
          bg: '#1a1a1a',
          track: '#242424',
          clip: '#4a9eff',
          audio: '#10b981',
          subtitle: '#f59e0b',
          marker: '#ef4444',
        }
      }
    }
  }
}
```

---

## Typography

### Font Family

**Primary Font**: [Inter](https://fonts.google.com/specimen/Inter)
- Modern, highly legible sans-serif
- Excellent for UI elements
- Variable font support

**Secondary Font**: [JetBrains Mono](https://fonts.google.com/specimen/JetBrains+Mono)
- For code, timestamps, technical values
- Monospace for alignment

**Accent Font**: [Poppins](https://fonts.google.com/specimen/Poppins)
- For headings and emphasis
- Optional, use sparingly

### Google Fonts Import
```html
<!-- In <head> -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
```

### Font Styles

**Headings**
```css
.h1 {
  font-family: 'Poppins', sans-serif;
  font-size: 2.5rem;      /* 40px */
  font-weight: 700;
  line-height: 1.2;
  letter-spacing: -0.02em;
}

.h2 {
  font-family: 'Poppins', sans-serif;
  font-size: 2rem;        /* 32px */
  font-weight: 700;
  line-height: 1.3;
  letter-spacing: -0.01em;
}

.h3 {
  font-family: 'Poppins', sans-serif;
  font-size: 1.5rem;      /* 24px */
  font-weight: 600;
  line-height: 1.4;
}

.h4 {
  font-family: 'Inter', sans-serif;
  font-size: 1.25rem;     /* 20px */
  font-weight: 600;
  line-height: 1.5;
}

.h5 {
  font-family: 'Inter', sans-serif;
  font-size: 1rem;        /* 16px */
  font-weight: 600;
  line-height: 1.5;
}
```

**Body Text**
```css
.text-base {
  font-family: 'Inter', sans-serif;
  font-size: 1rem;        /* 16px */
  font-weight: 400;
  line-height: 1.6;
}

.text-sm {
  font-family: 'Inter', sans-serif;
  font-size: 0.875rem;    /* 14px */
  font-weight: 400;
  line-height: 1.5;
}

.text-xs {
  font-family: 'Inter', sans-serif;
  font-size: 0.75rem;     /* 12px */
  font-weight: 400;
  line-height: 1.4;
}

.text-mono {
  font-family: 'JetBrains Mono', monospace;
  font-size: 0.875rem;    /* 14px */
  font-weight: 400;
}
```

### Tailwind Typography Classes
```html
<!-- Headings -->
<h1 class="font-poppins text-4xl font-bold tracking-tight">
<h2 class="font-poppins text-3xl font-bold tracking-tight">
<h3 class="font-poppins text-2xl font-semibold">

<!-- Body -->
<p class="font-inter text-base text-dark-text-primary">
<span class="font-inter text-sm text-dark-text-secondary">

<!-- Monospace -->
<code class="font-mono text-sm">
<time class="font-mono text-xs text-dark-text-tertiary">
```

---

## Layout System

### Container Widths
```css
.container-fluid {
  width: 100%;
  padding: 0 1rem;
}

.container-80 {
  width: 80%;
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 1rem;
}

.container-narrow {
  width: 70%;
  max-width: 1200px;
  margin: 0 auto;
}
```

### Grid System
```css
/* 12-column grid */
.grid-12 {
  display: grid;
  grid-template-columns: repeat(12, 1fr);
  gap: 1rem;
}

/* Common layouts */
.layout-sidebar {
  display: grid;
  grid-template-columns: 280px 1fr;
  gap: 1.5rem;
}

.layout-thirds {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
}
```

### Spacing Scale
```css
--space-xs: 0.25rem;   /* 4px */
--space-sm: 0.5rem;    /* 8px */
--space-md: 1rem;      /* 16px */
--space-lg: 1.5rem;    /* 24px */
--space-xl: 2rem;      /* 32px */
--space-2xl: 3rem;     /* 48px */
--space-3xl: 4rem;     /* 64px */
```

### Application Structure
```html
<body class="bg-dark-bg-primary text-dark-text-primary font-inter">
  <!-- Header: Full Width Background -->
  <header id="app-header" class="bg-dark-bg-secondary border-b border-dark-border-primary">
    <div class="container-80 py-4">
      <!-- Logo, Navigation, Theme Toggle -->
    </div>
  </header>

  <!-- Main Content: 80% Width -->
  <main id="app-main" class="container-80 py-6">
    <!-- Editor Interface -->
  </main>

  <!-- Footer: Full Width Background -->
  <footer id="app-footer" class="bg-dark-bg-secondary border-t border-dark-border-primary mt-auto">
    <div class="container-80 py-4">
      <!-- Footer Content -->
    </div>
  </footer>
</body>
```

---

## Component Library

### 1. Buttons

**Primary Button**
```html
<button id="btn-primary-example" class="
  px-6 py-2.5
  bg-accent-primary hover:bg-accent-hover active:bg-accent-active
  text-white font-medium text-sm
  rounded-lg
  transition-all duration-200
  focus:outline-none focus:ring-2 focus:ring-accent-primary focus:ring-offset-2 focus:ring-offset-dark-bg-primary
  disabled:opacity-50 disabled:cursor-not-allowed
  inline-flex items-center gap-2
">
  <i class="fas fa-play" id="btn-primary-icon"></i>
  <span id="btn-primary-text">Process Video</span>
</button>
```

**Secondary Button**
```html
<button id="btn-secondary-example" class="
  px-6 py-2.5
  bg-dark-bg-tertiary hover:bg-dark-bg-hover active:bg-dark-bg-active
  text-dark-text-primary font-medium text-sm
  border border-dark-border-primary
  rounded-lg
  transition-all duration-200
  focus:outline-none focus:ring-2 focus:ring-accent-primary focus:ring-offset-2 focus:ring-offset-dark-bg-primary
  inline-flex items-center gap-2
">
  <i class="fas fa-save" id="btn-secondary-icon"></i>
  <span id="btn-secondary-text">Save Draft</span>
</button>
```

**Icon Button**
```html
<button id="btn-icon-example" class="
  w-10 h-10
  bg-dark-bg-tertiary hover:bg-dark-bg-hover
  text-dark-text-primary
  rounded-lg
  transition-all duration-200
  flex items-center justify-center
  focus:outline-none focus:ring-2 focus:ring-accent-primary
" aria-label="Play video">
  <i class="fas fa-play" id="btn-icon-play"></i>
</button>
```

**Button Sizes**
```html
<!-- Small -->
<button id="btn-sm" class="px-3 py-1.5 text-xs">Small</button>

<!-- Medium (Default) -->
<button id="btn-md" class="px-6 py-2.5 text-sm">Medium</button>

<!-- Large -->
<button id="btn-lg" class="px-8 py-3 text-base">Large</button>
```

### 2. Input Fields

**Text Input**
```html
<div id="input-group-example" class="space-y-2">
  <label id="input-label" for="video-title" class="block text-sm font-medium text-dark-text-primary">
    Video Title
  </label>
  <input
    type="text"
    id="video-title"
    name="video-title"
    placeholder="Enter video title"
    class="
      w-full px-4 py-2.5
      bg-dark-bg-tertiary
      border border-dark-border-primary
      text-dark-text-primary placeholder-dark-text-tertiary
      rounded-lg
      focus:outline-none focus:ring-2 focus:ring-accent-primary focus:border-transparent
      transition-all duration-200
    "
  >
  <p id="input-hint" class="text-xs text-dark-text-tertiary">
    This will be displayed as the video filename
  </p>
</div>
```

**Textarea**
```html
<textarea
  id="ai-prompt"
  rows="4"
  placeholder="Describe what you want to edit..."
  class="
    w-full px-4 py-3
    bg-dark-bg-tertiary
    border border-dark-border-primary
    text-dark-text-primary placeholder-dark-text-tertiary
    rounded-lg
    focus:outline-none focus:ring-2 focus:ring-accent-primary
    transition-all duration-200
    resize-none
  "
></textarea>
```

**Select Dropdown**
```html
<select
  id="export-quality"
  class="
    w-full px-4 py-2.5
    bg-dark-bg-tertiary
    border border-dark-border-primary
    text-dark-text-primary
    rounded-lg
    focus:outline-none focus:ring-2 focus:ring-accent-primary
    transition-all duration-200
    cursor-pointer
  "
>
  <option value="720p">720p HD</option>
  <option value="1080p" selected>1080p Full HD</option>
  <option value="4k">4K Ultra HD</option>
</select>
```

### 3. Cards & Panels

**Card Component**
```html
<div id="card-example" class="
  bg-dark-bg-secondary
  border border-dark-border-primary
  rounded-xl
  overflow-hidden
  transition-all duration-200
  hover:border-dark-border-focus
">
  <!-- Card Header -->
  <div id="card-header" class="px-6 py-4 border-b border-dark-border-primary">
    <h3 id="card-title" class="text-lg font-semibold text-dark-text-primary">
      Video Information
    </h3>
  </div>

  <!-- Card Body -->
  <div id="card-body" class="px-6 py-4 space-y-3">
    <div id="card-content-row-1" class="flex justify-between items-center">
      <span id="card-label-1" class="text-sm text-dark-text-secondary">Duration</span>
      <span id="card-value-1" class="text-sm font-medium text-dark-text-primary font-mono">02:45:30</span>
    </div>
    <div id="card-content-row-2" class="flex justify-between items-center">
      <span id="card-label-2" class="text-sm text-dark-text-secondary">Resolution</span>
      <span id="card-value-2" class="text-sm font-medium text-dark-text-primary font-mono">1920x1080</span>
    </div>
  </div>

  <!-- Card Footer (Optional) -->
  <div id="card-footer" class="px-6 py-4 bg-dark-bg-tertiary border-t border-dark-border-primary">
    <button id="card-action-btn" class="text-sm text-accent-primary hover:text-accent-hover font-medium">
      View Details
    </button>
  </div>
</div>
```

### 4. Modal / Dialog

```html
<!-- Modal Overlay -->
<div id="modal-overlay" class="
  fixed inset-0 z-50
  bg-black bg-opacity-75
  backdrop-blur-sm
  flex items-center justify-center
  p-4
  hidden
">
  <!-- Modal Container -->
  <div id="modal-container" class="
    w-full max-w-2xl
    bg-dark-bg-secondary
    border border-dark-border-primary
    rounded-2xl
    shadow-2xl
    overflow-hidden
    transform transition-all duration-300
  ">
    <!-- Modal Header -->
    <div id="modal-header" class="
      px-6 py-4
      border-b border-dark-border-primary
      flex items-center justify-between
    ">
      <h2 id="modal-title" class="text-xl font-semibold text-dark-text-primary">
        Export Video
      </h2>
      <button id="modal-close-btn" class="
        w-8 h-8
        rounded-lg
        text-dark-text-secondary hover:text-dark-text-primary
        hover:bg-dark-bg-hover
        transition-all duration-200
        flex items-center justify-center
      ">
        <i class="fas fa-times" id="modal-close-icon"></i>
      </button>
    </div>

    <!-- Modal Body -->
    <div id="modal-body" class="px-6 py-6 space-y-4">
      <!-- Modal Content -->
    </div>

    <!-- Modal Footer -->
    <div id="modal-footer" class="
      px-6 py-4
      bg-dark-bg-tertiary
      border-t border-dark-border-primary
      flex items-center justify-end gap-3
    ">
      <button id="modal-cancel-btn" class="px-6 py-2.5 text-sm">Cancel</button>
      <button id="modal-confirm-btn" class="px-6 py-2.5 text-sm bg-accent-primary">Export</button>
    </div>
  </div>
</div>
```

### 5. Notifications / Toasts

```html
<!-- Success Notification -->
<div id="notification-success" class="
  fixed top-4 right-4 z-50
  max-w-md
  bg-dark-bg-tertiary
  border-l-4 border-accent-success
  rounded-lg
  shadow-xl
  p-4
  flex items-start gap-3
  transform transition-all duration-300
  translate-x-0 opacity-100
">
  <div id="notification-icon-wrapper" class="
    flex-shrink-0
    w-10 h-10
    bg-accent-success bg-opacity-20
    rounded-full
    flex items-center justify-center
  ">
    <i class="fas fa-check text-accent-success" id="notification-icon"></i>
  </div>

  <div id="notification-content" class="flex-1 min-w-0">
    <h4 id="notification-title" class="text-sm font-semibold text-dark-text-primary mb-1">
      Video Exported Successfully
    </h4>
    <p id="notification-message" class="text-sm text-dark-text-secondary">
      Your video is ready to download
    </p>
  </div>

  <button id="notification-close-btn" class="
    flex-shrink-0
    text-dark-text-tertiary hover:text-dark-text-primary
    transition-colors duration-200
  ">
    <i class="fas fa-times" id="notification-close-icon"></i>
  </button>
</div>

<!-- Error Notification -->
<div id="notification-error" class="border-l-4 border-accent-error">
  <!-- Same structure, different border color -->
</div>

<!-- Warning Notification -->
<div id="notification-warning" class="border-l-4 border-accent-warning">
  <!-- Same structure, different border color -->
</div>

<!-- Info Notification -->
<div id="notification-info" class="border-l-4 border-accent-info">
  <!-- Same structure, different border color -->
</div>
```

### 6. Loading States

**Spinner**
```html
<div id="spinner-wrapper" class="flex items-center justify-center">
  <div id="spinner" class="
    w-12 h-12
    border-4 border-dark-bg-hover
    border-t-accent-primary
    rounded-full
    animate-spin
  "></div>
</div>
```

**Skeleton Loader**
```html
<div id="skeleton-card" class="animate-pulse space-y-4">
  <div id="skeleton-header" class="h-4 bg-dark-bg-hover rounded w-3/4"></div>
  <div id="skeleton-line-1" class="h-3 bg-dark-bg-hover rounded w-full"></div>
  <div id="skeleton-line-2" class="h-3 bg-dark-bg-hover rounded w-5/6"></div>
</div>
```

**Progress Bar**
```html
<div id="progress-bar-wrapper" class="space-y-2">
  <div id="progress-info" class="flex justify-between items-center">
    <span id="progress-label" class="text-sm text-dark-text-secondary">Processing video...</span>
    <span id="progress-value" class="text-sm font-mono text-dark-text-primary">45%</span>
  </div>
  <div id="progress-track" class="w-full h-2 bg-dark-bg-hover rounded-full overflow-hidden">
    <div id="progress-fill" class="h-full bg-accent-primary rounded-full transition-all duration-300" style="width: 45%"></div>
  </div>
</div>
```

### 7. Navigation

**Top Navigation Bar**
```html
<nav id="main-nav" class="flex items-center justify-between">
  <!-- Logo -->
  <div id="nav-logo" class="flex items-center gap-3">
    <i class="fas fa-video text-accent-primary text-2xl" id="logo-icon"></i>
    <span id="logo-text" class="text-xl font-bold text-dark-text-primary">VideoAI Editor</span>
  </div>

  <!-- Nav Links -->
  <ul id="nav-links" class="flex items-center gap-1">
    <li id="nav-item-1">
      <a href="#" id="nav-link-1" class="
        px-4 py-2
        text-sm font-medium text-dark-text-secondary hover:text-dark-text-primary
        hover:bg-dark-bg-hover
        rounded-lg
        transition-all duration-200
      ">
        <i class="fas fa-home mr-2" id="nav-icon-1"></i>
        Projects
      </a>
    </li>
    <li id="nav-item-2">
      <a href="#" id="nav-link-2" class="px-4 py-2 text-sm font-medium">
        <i class="fas fa-folder mr-2" id="nav-icon-2"></i>
        Library
      </a>
    </li>
  </ul>

  <!-- Right Actions -->
  <div id="nav-actions" class="flex items-center gap-3">
    <!-- Theme Toggle -->
    <button id="theme-toggle-btn" class="
      w-10 h-10
      text-dark-text-secondary hover:text-dark-text-primary
      hover:bg-dark-bg-hover
      rounded-lg
      transition-all duration-200
      flex items-center justify-center
    " aria-label="Toggle theme">
      <i class="fas fa-moon" id="theme-icon"></i>
    </button>

    <!-- User Menu -->
    <button id="user-menu-btn" class="
      flex items-center gap-2
      px-3 py-2
      hover:bg-dark-bg-hover
      rounded-lg
      transition-all duration-200
    ">
      <img id="user-avatar" src="https://api.dicebear.com/7.x/avataaars/svg?seed=user" alt="User" class="w-8 h-8 rounded-full">
      <span id="user-name" class="text-sm font-medium text-dark-text-primary">John Doe</span>
    </button>
  </div>
</nav>
```

**Sidebar Navigation**
```html
<aside id="sidebar" class="w-64 bg-dark-bg-secondary border-r border-dark-border-primary h-full">
  <nav id="sidebar-nav" class="p-4 space-y-2">
    <a href="#" id="sidebar-link-1" class="
      flex items-center gap-3
      px-4 py-3
      text-sm font-medium text-dark-text-primary
      bg-dark-bg-hover
      rounded-lg
      border-l-4 border-accent-primary
    ">
      <i class="fas fa-cut w-5" id="sidebar-icon-1"></i>
      <span id="sidebar-text-1">Editor</span>
    </a>

    <a href="#" id="sidebar-link-2" class="
      flex items-center gap-3
      px-4 py-3
      text-sm font-medium text-dark-text-secondary hover:text-dark-text-primary
      hover:bg-dark-bg-hover
      rounded-lg
      transition-all duration-200
    ">
      <i class="fas fa-robot w-5" id="sidebar-icon-2"></i>
      <span id="sidebar-text-2">AI Suggestions</span>
    </a>
  </nav>
</aside>
```

### 8. Timeline Component

```html
<div id="timeline-container" class="bg-timeline-bg border border-dark-border-primary rounded-xl overflow-hidden">
  <!-- Timeline Header -->
  <div id="timeline-header" class="px-4 py-3 bg-dark-bg-tertiary border-b border-dark-border-primary flex items-center justify-between">
    <div id="timeline-controls" class="flex items-center gap-2">
      <button id="play-btn" class="w-8 h-8 flex items-center justify-center text-dark-text-primary hover:bg-dark-bg-hover rounded">
        <i class="fas fa-play" id="play-icon"></i>
      </button>
      <span id="current-time" class="font-mono text-sm text-dark-text-primary">00:00:00</span>
      <span id="time-separator" class="text-dark-text-tertiary">/</span>
      <span id="total-time" class="font-mono text-sm text-dark-text-secondary">00:05:30</span>
    </div>

    <div id="timeline-zoom" class="flex items-center gap-2">
      <button id="zoom-out-btn" class="w-8 h-8 text-dark-text-secondary hover:text-dark-text-primary">
        <i class="fas fa-search-minus" id="zoom-out-icon"></i>
      </button>
      <span id="zoom-level" class="text-xs text-dark-text-tertiary font-mono">100%</span>
      <button id="zoom-in-btn" class="w-8 h-8 text-dark-text-secondary hover:text-dark-text-primary">
        <i class="fas fa-search-plus" id="zoom-in-icon"></i>
      </button>
    </div>
  </div>

  <!-- Timeline Tracks -->
  <div id="timeline-tracks" class="p-4 space-y-2">
    <!-- Video Track -->
    <div id="track-video" class="relative h-20 bg-timeline-track rounded-lg overflow-hidden">
      <div id="video-clip-1" class="absolute top-0 left-0 h-full bg-timeline-clip rounded" style="width: 60%; left: 0%">
        <div id="clip-label-1" class="px-3 py-2 text-xs font-medium text-white">Video Clip 1</div>
      </div>
    </div>

    <!-- Audio Track -->
    <div id="track-audio" class="relative h-16 bg-timeline-track rounded-lg overflow-hidden">
      <canvas id="waveform-canvas" class="w-full h-full"></canvas>
    </div>

    <!-- Subtitle Track -->
    <div id="track-subtitle" class="relative h-12 bg-timeline-track rounded-lg overflow-hidden">
      <div id="subtitle-segment-1" class="absolute top-0 h-full bg-timeline-subtitle rounded" style="width: 30%; left: 10%"></div>
    </div>
  </div>

  <!-- Playhead -->
  <div id="playhead" class="absolute top-0 w-0.5 bg-timeline-marker z-10" style="left: 25%; height: 100%">
    <div id="playhead-handle" class="absolute -top-2 -left-2 w-4 h-4 bg-timeline-marker rounded-full"></div>
  </div>
</div>
```

---

## Icons (Font Awesome 6)

### Icon Library

**CDN Import**
```html
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
```

### Common Icons Used

**Video Operations**
```html
<i class="fas fa-play"></i>           <!-- Play -->
<i class="fas fa-pause"></i>          <!-- Pause -->
<i class="fas fa-stop"></i>           <!-- Stop -->
<i class="fas fa-forward"></i>        <!-- Forward -->
<i class="fas fa-backward"></i>       <!-- Backward -->
<i class="fas fa-cut"></i>            <!-- Cut/Trim -->
<i class="fas fa-scissors"></i>       <!-- Split -->
<i class="fas fa-crop"></i>           <!-- Crop -->
<i class="fas fa-video"></i>          <!-- Video -->
<i class="fas fa-film"></i>           <!-- Film -->
```

**Audio Operations**
```html
<i class="fas fa-volume-up"></i>      <!-- Audio -->
<i class="fas fa-volume-mute"></i>    <!-- Mute -->
<i class="fas fa-music"></i>          <!-- Music -->
<i class="fas fa-waveform-lines"></i> <!-- Waveform -->
<i class="fas fa-microphone"></i>     <!-- Microphone -->
```

**Editing Tools**
```html
<i class="fas fa-text"></i>           <!-- Text -->
<i class="fas fa-closed-captioning"></i> <!-- Subtitles -->
<i class="fas fa-magic"></i>          <!-- Effects -->
<i class="fas fa-palette"></i>        <!-- Color -->
<i class="fas fa-adjust"></i>         <!-- Adjust -->
<i class="fas fa-sliders-h"></i>      <!-- Settings -->
<i class="fas fa-layer-group"></i>    <!-- Layers -->
```

**AI Features**
```html
<i class="fas fa-robot"></i>          <!-- AI -->
<i class="fas fa-brain"></i>          <!-- AI Brain -->
<i class="fas fa-sparkles"></i>       <!-- Magic/AI -->
<i class="fas fa-wand-magic-sparkles"></i> <!-- AI Enhance -->
```

**File Operations**
```html
<i class="fas fa-upload"></i>         <!-- Upload -->
<i class="fas fa-download"></i>       <!-- Download -->
<i class="fas fa-save"></i>           <!-- Save -->
<i class="fas fa-folder"></i>         <!-- Folder -->
<i class="fas fa-file-video"></i>     <!-- Video File -->
<i class="fas fa-cloud-upload"></i>   <!-- Cloud Upload -->
```

**UI Controls**
```html
<i class="fas fa-search"></i>         <!-- Search -->
<i class="fas fa-search-plus"></i>    <!-- Zoom In -->
<i class="fas fa-search-minus"></i>   <!-- Zoom Out -->
<i class="fas fa-times"></i>          <!-- Close -->
<i class="fas fa-check"></i>          <!-- Success -->
<i class="fas fa-exclamation-triangle"></i> <!-- Warning -->
<i class="fas fa-info-circle"></i>    <!-- Info -->
<i class="fas fa-cog"></i>            <!-- Settings -->
<i class="fas fa-ellipsis-v"></i>     <!-- More Options -->
```

**Theme Toggle**
```html
<i class="fas fa-moon"></i>           <!-- Dark Mode -->
<i class="fas fa-sun"></i>            <!-- Light Mode -->
```

### Icon Usage Guidelines

```html
<!-- Icon with text -->
<button id="btn-with-icon" class="inline-flex items-center gap-2">
  <i class="fas fa-upload" id="icon-upload"></i>
  <span id="btn-text-upload">Upload Video</span>
</button>

<!-- Icon only (must have aria-label) -->
<button id="btn-icon-only" aria-label="Play video">
  <i class="fas fa-play" id="icon-play-only"></i>
</button>

<!-- Icon sizes -->
<i class="fas fa-video text-xs" id="icon-xs"></i>      <!-- 12px -->
<i class="fas fa-video text-sm" id="icon-sm"></i>      <!-- 14px -->
<i class="fas fa-video text-base" id="icon-base"></i>  <!-- 16px -->
<i class="fas fa-video text-lg" id="icon-lg"></i>      <!-- 18px -->
<i class="fas fa-video text-xl" id="icon-xl"></i>      <!-- 20px -->
<i class="fas fa-video text-2xl" id="icon-2xl"></i>    <!-- 24px -->
<i class="fas fa-video text-3xl" id="icon-3xl"></i>    <!-- 30px -->
```

---

## Animation & Transitions

### Transition Durations
```css
--transition-fast: 150ms;
--transition-normal: 200ms;
--transition-slow: 300ms;
```

### Common Animations

**Hover Effects**
```css
/* Button hover lift */
.btn-hover-lift {
  transition: transform 200ms ease, box-shadow 200ms ease;
}
.btn-hover-lift:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Icon spin on hover */
.icon-spin-hover:hover i {
  animation: spin 0.6s ease-in-out;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
```

**Micro-interactions**
```css
/* Scale on click */
.scale-click:active {
  transform: scale(0.95);
  transition: transform 100ms ease;
}

/* Ripple effect */
.ripple {
  position: relative;
  overflow: hidden;
}
.ripple::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 0;
  height: 0;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.3);
  transform: translate(-50%, -50%);
  transition: width 0.6s, height 0.6s;
}
.ripple:active::after {
  width: 300px;
  height: 300px;
}
```

**Page Transitions**
```css
/* Fade in */
.fade-in {
  animation: fadeIn 300ms ease-in;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

/* Slide up */
.slide-up {
  animation: slideUp 300ms ease-out;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
```

---

## Theme Switching Implementation

### JavaScript Theme Toggle

```javascript
// theme-toggle.js

class ThemeManager {
  constructor() {
    this.theme = localStorage.getItem('theme') || 'dark';
    this.init();
  }

  init() {
    // Apply saved theme
    this.applyTheme(this.theme);

    // Setup toggle button
    const toggleBtn = document.getElementById('theme-toggle-btn');
    if (toggleBtn) {
      toggleBtn.addEventListener('click', () => this.toggle());
    }
  }

  applyTheme(theme) {
    const html = document.documentElement;

    if (theme === 'dark') {
      html.classList.add('dark');
      this.updateIcon('moon');
    } else {
      html.classList.remove('dark');
      this.updateIcon('sun');
    }

    this.theme = theme;
    localStorage.setItem('theme', theme);
  }

  toggle() {
    const newTheme = this.theme === 'dark' ? 'light' : 'dark';
    this.applyTheme(newTheme);

    // Animate transition
    document.body.style.transition = 'background-color 300ms ease, color 300ms ease';
    setTimeout(() => {
      document.body.style.transition = '';
    }, 300);
  }

  updateIcon(icon) {
    const iconElement = document.getElementById('theme-icon');
    if (iconElement) {
      iconElement.className = icon === 'moon' ? 'fas fa-moon' : 'fas fa-sun';
    }
  }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
  new ThemeManager();
});
```

### CSS Theme Variables

```css
/* styles.css */

:root {
  /* Light theme (default fallback) */
  --bg-primary: #ffffff;
  --text-primary: #0f0f0f;
  /* ... other light theme variables ... */
}

.dark {
  /* Dark theme variables */
  --bg-primary: #0f0f0f;
  --text-primary: #f5f5f5;
  /* ... other dark theme variables ... */
}

/* Apply variables */
body {
  background-color: var(--bg-primary);
  color: var(--text-primary);
  transition: background-color 300ms ease, color 300ms ease;
}
```

---

## Responsive Design

### Breakpoints
```css
/* Tailwind default breakpoints */
sm: 640px   /* Small devices (landscape phones) */
md: 768px   /* Medium devices (tablets) */
lg: 1024px  /* Large devices (desktops) */
xl: 1280px  /* Extra large devices (large desktops) */
2xl: 1536px /* 2X large devices (larger desktops) */
```

### Responsive Patterns

**Mobile-First Approach**
```html
<!-- Stack on mobile, grid on desktop -->
<div id="responsive-grid" class="
  grid grid-cols-1
  md:grid-cols-2
  lg:grid-cols-3
  gap-4
">
  <!-- Cards -->
</div>

<!-- Hide on mobile, show on desktop -->
<aside id="sidebar-desktop" class="hidden lg:block">
  <!-- Sidebar content -->
</aside>

<!-- Show on mobile, hide on desktop -->
<button id="menu-toggle-mobile" class="lg:hidden">
  <i class="fas fa-bars" id="menu-icon"></i>
</button>
```

**Responsive Typography**
```html
<h1 id="responsive-heading" class="
  text-2xl sm:text-3xl md:text-4xl lg:text-5xl
  font-bold
">
  Video Editor
</h1>
```

**Responsive Spacing**
```html
<div id="responsive-container" class="
  px-4 sm:px-6 md:px-8
  py-4 sm:py-6 md:py-8
">
  <!-- Content -->
</div>
```

---

## Accessibility Guidelines

### ARIA Labels
```html
<!-- Always include aria-label for icon-only buttons -->
<button id="delete-btn" aria-label="Delete video">
  <i class="fas fa-trash" id="delete-icon"></i>
</button>

<!-- Use aria-describedby for additional context -->
<input
  id="video-title-input"
  type="text"
  aria-describedby="title-help"
>
<p id="title-help" class="text-sm text-dark-text-tertiary">
  Enter a descriptive title for your video
</p>
```

### Focus States
```css
/* Always provide visible focus indicators */
button:focus,
input:focus,
select:focus,
textarea:focus {
  outline: none;
  ring: 2px solid var(--accent-primary);
  ring-offset: 2px;
}

/* Skip to content link */
.skip-to-content {
  position: absolute;
  top: -40px;
  left: 0;
  background: var(--accent-primary);
  color: white;
  padding: 8px 16px;
  text-decoration: none;
  z-index: 100;
}
.skip-to-content:focus {
  top: 0;
}
```

### Keyboard Navigation
```javascript
// Enable keyboard shortcuts
document.addEventListener('keydown', (e) => {
  // Space = Play/Pause
  if (e.code === 'Space' && e.target.tagName !== 'INPUT') {
    e.preventDefault();
    togglePlayPause();
  }

  // Arrow Left = Seek backward
  if (e.code === 'ArrowLeft') {
    seekBackward();
  }

  // Arrow Right = Seek forward
  if (e.code === 'ArrowRight') {
    seekForward();
  }
});
```

---

## Performance Optimization

### CSS Best Practices
```css
/* Use transform instead of position for animations */
.slide-in {
  transform: translateX(0);
  transition: transform 300ms ease;
}

/* Avoid animating expensive properties */
/* BAD */
.bad-animation {
  transition: width 300ms, height 300ms;
}

/* GOOD */
.good-animation {
  transition: transform 300ms, opacity 300ms;
}

/* Use will-change sparingly */
.timeline-playhead {
  will-change: transform;
}
```

### Image Optimization
```html
<!-- Use proper image formats -->
<img
  id="thumbnail-img"
  src="thumbnail.webp"
  alt="Video thumbnail"
  loading="lazy"
  width="320"
  height="180"
>

<!-- Use DiceBear for avatars -->
<img
  id="user-avatar-img"
  src="https://api.dicebear.com/7.x/avataaars/svg?seed=username"
  alt="User avatar"
  class="w-10 h-10 rounded-full"
>
```

---

## Code Examples

### Complete Page Template

```html
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AI Video Editor</title>

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <!-- Custom Styles -->
  <link rel="stylesheet" href="/assets/css/editor.css">

  <!-- Tailwind Config -->
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          fontFamily: {
            'inter': ['Inter', 'sans-serif'],
            'mono': ['JetBrains Mono', 'monospace'],
            'poppins': ['Poppins', 'sans-serif'],
          },
          colors: {
            dark: {
              bg: {
                primary: '#0f0f0f',
                secondary: '#1a1a1a',
                tertiary: '#242424',
                hover: '#2a2a2a',
                active: '#303030',
              },
              text: {
                primary: '#f5f5f5',
                secondary: '#a0a0a0',
                tertiary: '#6b6b6b',
              },
              border: {
                primary: '#333333',
                secondary: '#404040',
                focus: '#4a9eff',
              }
            },
            accent: {
              primary: '#4a9eff',
              hover: '#6ab0ff',
              active: '#3a8eef',
              success: '#10b981',
              warning: '#f59e0b',
              error: '#ef4444',
              info: '#3b82f6',
            },
            timeline: {
              bg: '#1a1a1a',
              track: '#242424',
              clip: '#4a9eff',
              audio: '#10b981',
              subtitle: '#f59e0b',
              marker: '#ef4444',
            }
          }
        }
      }
    }
  </script>
</head>
<body class="bg-dark-bg-primary text-dark-text-primary font-inter min-h-screen flex flex-col">

  <!-- Header -->
  <header id="app-header" class="bg-dark-bg-secondary border-b border-dark-border-primary">
    <div class="container mx-auto px-6 py-4" style="width: 80%;">
      <nav id="main-nav" class="flex items-center justify-between">
        <!-- Logo -->
        <div id="nav-logo" class="flex items-center gap-3">
          <i class="fas fa-video text-accent-primary text-2xl" id="logo-icon"></i>
          <span id="logo-text" class="text-xl font-bold font-poppins">VideoAI Editor</span>
        </div>

        <!-- Navigation Links -->
        <ul id="nav-links" class="flex items-center gap-1">
          <li id="nav-item-editor">
            <a href="#editor" id="nav-link-editor" class="px-4 py-2 text-sm font-medium text-dark-text-primary bg-dark-bg-hover rounded-lg">
              <i class="fas fa-cut mr-2" id="nav-icon-editor"></i>
              Editor
            </a>
          </li>
          <li id="nav-item-library">
            <a href="#library" id="nav-link-library" class="px-4 py-2 text-sm font-medium text-dark-text-secondary hover:text-dark-text-primary hover:bg-dark-bg-hover rounded-lg transition-all duration-200">
              <i class="fas fa-folder mr-2" id="nav-icon-library"></i>
              Library
            </a>
          </li>
        </ul>

        <!-- Right Actions -->
        <div id="nav-actions" class="flex items-center gap-3">
          <button id="theme-toggle-btn" class="w-10 h-10 text-dark-text-secondary hover:text-dark-text-primary hover:bg-dark-bg-hover rounded-lg transition-all duration-200 flex items-center justify-center" aria-label="Toggle theme">
            <i class="fas fa-moon" id="theme-icon"></i>
          </button>

          <button id="user-menu-btn" class="flex items-center gap-2 px-3 py-2 hover:bg-dark-bg-hover rounded-lg transition-all duration-200">
            <img id="user-avatar" src="https://api.dicebear.com/7.x/avataaars/svg?seed=user" alt="User" class="w-8 h-8 rounded-full">
            <span id="user-name" class="text-sm font-medium">John Doe</span>
          </button>
        </div>
      </nav>
    </div>
  </header>

  <!-- Main Content -->
  <main id="app-main" class="flex-1 py-6">
    <div class="container mx-auto px-6" style="width: 80%;">
      <!-- Your editor content here -->
      <h1 id="page-title" class="text-4xl font-bold font-poppins mb-6">Video Editor</h1>

      <!-- Example Card -->
      <div id="example-card" class="bg-dark-bg-secondary border border-dark-border-primary rounded-xl p-6">
        <p id="example-text" class="text-dark-text-secondary">Your editor interface goes here...</p>
      </div>
    </div>
  </main>

  <!-- Footer -->
  <footer id="app-footer" class="bg-dark-bg-secondary border-t border-dark-border-primary mt-auto">
    <div class="container mx-auto px-6 py-4" style="width: 80%;">
      <div id="footer-content" class="flex items-center justify-between text-sm text-dark-text-tertiary">
        <p id="copyright">&copy; 2025 VideoAI Editor. All rights reserved.</p>
        <div id="footer-links" class="flex items-center gap-4">
          <a href="#" id="footer-link-privacy" class="hover:text-dark-text-primary transition-colors duration-200">Privacy</a>
          <a href="#" id="footer-link-terms" class="hover:text-dark-text-primary transition-colors duration-200">Terms</a>
          <a href="#" id="footer-link-help" class="hover:text-dark-text-primary transition-colors duration-200">Help</a>
        </div>
      </div>
    </div>
  </footer>

  <!-- Scripts -->
  <script src="/assets/js/theme-toggle.js"></script>
  <script src="/assets/js/editor.js"></script>
</body>
</html>
```

---

## Summary

This style guide provides a complete design system for the AI Video Editor with:

- **Dark-first design** with seamless light theme switching
- **Modern typography** using Inter, JetBrains Mono, and Poppins from Google Fonts
- **Professional color palette** optimized for video editing workflows
- **Comprehensive component library** with Tailwind CSS classes
- **Font Awesome 6 icons** for consistent iconography
- **Smooth animations** and micro-interactions
- **Responsive design** patterns for all screen sizes
- **Accessibility** best practices (ARIA, keyboard navigation)
- **Performance optimization** guidelines

All components follow the 80% width container requirement with full-width backgrounds, unique IDs on every element, and custom JavaScript notifications instead of native alerts.
