<div class="{{$viewClass['form-group']}}">

    <label class="{{$viewClass['label']}} control-label">{{$label}}</label>

    <div class="{{$viewClass['field']}}">

        @include('admin::form.error')

        <div {!! $attributes !!} style="width: 100%; height: 100%;">
            <p>{!! $value !!}</p>
        </div>

        <input type="hidden" name="{{$name}}" value="{{ old($column, $value) }}" />

        @include('admin::form.help-block')

    </div>
</div>

<script require="@wang-editor" init="{!! $selector !!}">
    var E = window.wangEditor
    var editor = new E('#' + id);
    editor.customConfig.zIndex = 0
    editor.customConfig.uploadImgShowBase64 = true
    editor.customConfig.onchange = function (html) {
        $this.parents('.form-field').find('input[name={{ $name }}]').val(html);
    }
    editor.create()
</script>

