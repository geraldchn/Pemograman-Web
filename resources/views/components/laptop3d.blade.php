@props([
    'title' => 'Techcare',        // big text on the laptop screen
    'subtitle' => null,           // small line under the title
    'badge' => null,              // status chip on the screen, e.g. "Repair in Progress"
    'color' => 'accent',          // accent | success | warning | danger (tints glow, LED, chip)
    'step' => null,               // 0-6 progress position, or null to hide the progress bar
    'height' => 340,              // px height of the stage
    'scale' => 1,
    'ry' => -28,                  // starting yaw (degrees)
])
@php
    $rows = [14, 14, 14, 14];     // normal key rows; the bottom row is built by hand below
@endphp
<div class="l3d" data-l3d data-color="{{ $color }}" data-ry="{{ $ry }}" tabindex="0" role="img"
     aria-label="3D laptop: {{ $title }}{{ $badge ? ', '.$badge : '' }}. Drag or use arrow keys to rotate."
     style="--l3d-h: {{ $height }}px; --l3d-scale: {{ $scale }};">
    <div class="l3d-stage">
        <div class="l3d-laptop">
            <div class="f ground"></div>

            {{-- base: keyboard deck --}}
            <div class="base">
                <div class="f top">
                    <div class="keys">
                        @foreach ($rows as $r)
                            @for ($i = 0; $i < 14; $i++)
                                <i @class(['accent' => $r === 14 && $loop->first && $i === 13])></i>
                            @endfor
                        @endforeach
                        <i class="s3"></i><i class="s8"></i><i class="s3"></i>
                    </div>
                    <div class="pad"></div>
                </div>
                <div class="f bottom"></div>
                <div class="f front"><span class="led"></span></div>
                <div class="f back"></div>
                <div class="f left"></div>
                <div class="f right"></div>
            </div>

            {{-- lid: screen + outer shell --}}
            <div class="lid">
                <div class="f screen">
                    <div class="disp">
                        <div class="bar"><b></b><b></b><b></b></div>
                        <div class="mid">
                            <img src="{{ asset('images/logo/icon-on-dark.png') }}" alt="">
                            <div class="ttl">{{ $title }}</div>
                            @if ($subtitle)<div class="sub">{{ $subtitle }}</div>@endif
                            @if ($badge)<div class="chip">{{ $badge }}</div>@endif
                        </div>
                        @if (! is_null($step))
                            <div class="steps">
                                @for ($i = 0; $i < 7; $i++)
                                    <i @class(['on' => $i < $step || $step >= 6, 'now' => $i === $step && $step < 6])></i>
                                @endfor
                            </div>
                        @endif
                    </div>
                </div>
                <div class="f outer"><img src="{{ asset('images/logo/icon-on-dark.png') }}" alt=""></div>
                <div class="f e-top"></div>
                <div class="f e-bottom"></div>
                <div class="f e-left"></div>
                <div class="f e-right"></div>
            </div>
        </div>
    </div>
</div>
