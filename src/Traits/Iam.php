<?php

namespace m7\Iam\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use m7\Iam\Manager;

trait Iam {

    /**
     * @return mixed
     * @author Adam Ondrejkovic
     */
    public function getScopes()
    {
        $key = "{$this->id}-scopes";

        if (Cache::has($key)) {
            Log::info("Getting {$key} from cache");
            return Cache::get($key);
        } else {
            $scopes = iam_manager()->getUserScopes();

            if (!empty($scopes)) {
                Cache::put($key, $scopes, 60 * 60 * 6); // 6 hours
            }

            return $scopes;
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
