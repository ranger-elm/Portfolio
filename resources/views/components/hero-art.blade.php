{{-- Decorative hero artwork: an isometric stack of translucent planes with
     restrained code-inspired detail. Hidden from assistive technology.

     Placement uses logical offsets so the composition mirrors with the
     document direction, while each Latin code run is isolated with dir="ltr"
     so its punctuation never reorders inside Persian. --}}
<div class="hero-stage mx-auto w-full max-w-[17rem] sm:max-w-[21rem] lg:max-w-[25rem]" aria-hidden="true">
    <div class="hero-grid"></div>

    <span class="hero-tick hero-tick--start-top"></span>
    <span class="hero-tick hero-tick--end-bottom"></span>

    <div class="hero-rig">
        <div class="hero-plate"></div>
        <div class="hero-plate hero-plate--mid"></div>
        <div class="hero-plate hero-plate--top"></div>
    </div>

    <p class="hero-token"><span dir="ltr">php artisan serve</span></p>

    <p class="hero-code">
        <span dir="ltr" class="block">
            <span class="hero-syntax-key">Route</span><span class="hero-syntax-fn">::get</span>(<span class="hero-syntax-string">'/fa/projects'</span>)
        </span>
        <span dir="ltr" class="block">
            <span class="hero-syntax-key">config</span>(<span class="hero-syntax-string">'content.projects'</span>)
        </span>
    </p>

    <span class="hero-meta"><span dir="ltr">rotateX(56deg)</span></span>
</div>
