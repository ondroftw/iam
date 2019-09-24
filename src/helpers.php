<?php

if (!function_exists('iam_manager')) {
    /**
     * @return \m7\Iam\Manager
     * @author Adam Ondrejkovic
     */
    function iam_manager()
    {
        return app(\m7\Iam\Manager::class);
    }
}
