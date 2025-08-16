<?php

namespace App\Traits;

trait ResponseTrait
{
   
    public function success($data = null, $message = 'Operation successful')
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ]);
    }

    public function error($message = 'An error occurred', $code = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $code);
    }
}
