{{-- Decorative hero artwork: three translucent planes stacked in isometric space,
     assembled on load and annotated like a detail drawing. Hidden from assistive
     technology.

     Placement uses logical offsets so the composition mirrors with the document
     direction, while each Latin code run is isolated with dir="ltr" so its
     punctuation never reorders inside Persian. --}}
<div class="hero-stage mx-auto w-full max-w-[17rem] sm:max-w-[21rem] lg:max-w-[25rem]" data-hero-tilt aria-hidden="true">
    <div class="hero-grid"></div>

    <div class="hero-rig">
        <div class="hero-orbit">
            <div class="hero-layer">
                <div class="hero-plate hero-plate--base"></div>
            </div>
            <div class="hero-layer">
                <div class="hero-plate hero-plate--mid"></div>
            </div>
            <div class="hero-layer">
                <div class="hero-plate hero-plate--top"></div>
            </div>
        </div>
    </div>

    <p class="hero-code glow">
        <span dir="ltr" class="hero-line hero-line--prompt">php artisan serve<span class="hero-caret"></span></span>
        <span dir="ltr" class="hero-line">
            <span class="hero-syntax-key">Route</span><span class="hero-syntax-fn">::get</span>(<span class="hero-syntax-string">'/fa/projects'</span>)
        </span>
        <span dir="ltr" class="hero-line">
            <span class="hero-syntax-key">config</span>(<span class="hero-syntax-string">'content.projects'</span>)
        </span>
    </p>

    <span class="hero-meta"><span dir="ltr">preserve-3d</span></span>
</div>
