<?php

namespace Sancti\Http\Facades;

use Illuminate\Support\Facades\Facade;

class SanctiFacade extends Facade {
	protected static function getFacadeAccessor() {
		return 'sancti-facade';
	}
}