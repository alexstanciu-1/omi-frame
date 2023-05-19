@if ($_qengine_args['_rowi'])
	<span class='js-rm-flag' style='display: none;'></span>
	<input class='qc-rowi' type="hidden" name{{!$_qengine_args['_rowi'][1] ? "-x" : ""}}="{{$_qengine_args['_rowi'][0]}}" value="{{$_qengine_args['_rowi'][1]}}" />
@endif