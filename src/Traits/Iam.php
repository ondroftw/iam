<?php

namespace m7\Iam\Traits;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use m7\Iam\Manager;

trait Iam {

	/**
	 * @return array
	 * @author Adam Ondrejkovic
	 */
    public function getScopes()
    {
    	if (iam_manager()->isUserLoggedIn()) {
			return iam_manager()->getUserScopes();
		} else {
    		return [];
		}
    }

	/**
	 * @param $scopes
	 *
	 * @return bool
	 * @author Adam Ondrejkovic
	 */
    public function hasScope($scopes)
    {
        $userScopes = $this->getScopes();

        if (is_array($scopes)) {
            foreach ($scopes as $scope) {
                if (!in_array($scope, $userScopes)) {
                    return false;
                }
            }

            return true;
        } else {
            return in_array($scopes, $userScopes);
        }
    }
}
