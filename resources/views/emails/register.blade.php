<div class="row">
@lang('Welcome') {{ $user->name }}
</div>
<div class="row">
	<p> @lang('Confirm your email address') </p>
	<a href="{{ request()->getSchemeAndHttpHost() }}/api/activate/{{ $user->id }}/{{ $user->code }}" target="_blank"> Confirm email </a>
</div>