<div class="card mb-3 tc-stage">
    <div class="tc-stage-body">
        <x-laptop3d :title="$laptop->displayName()"
                    :subtitle="$booking ? $booking->code().' · '.\Illuminate\Support\Str::limit($booking->issue, 34) : ($laptop->serial_number ? 'S/N '.$laptop->serial_number : 'Not in service')"
                    :badge="$current ? $current->label() : 'No service yet'"
                    :color="$current ? $current->color() : 'accent'"
                    :step="$curStep"
                    :height="290" :scale="0.9" :ry="-24" />
    </div>
    <div class="tc-stage-hint"><i class="fa fa-hand-pointer"></i> Drag to rotate &middot; double-click to reset</div>
</div>
