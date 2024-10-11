@extends('yivic-base::layouts/simple')

@section('content')
	<div class="container">
		<h1><?php echo 'Setup WP App'; ?></h1>
		<div class="message-content">
			{!! $message !!}
		</div>
	</div>
@endsection

@if( ! empty($return_url) )
<script type="text/javascript">
	window.setTimeout(function(){
		window.location.href = '{!! esc_attr($return_url) !!}';
	}, 500);
</script>
@endif
