<?php

namespace App\Traits;

/**
 * Helpers de respuesta/flash legados del Controller base (spec
 * controladores-residuales-fase5, hijo 6): los 15 helpers y el array
 * $message se mueven TAL CUAL desde Controller.php, que queda como
 * clase mínima. $this->message lo consume Api/UserController (4 refs)
 * — no eliminarlo ni traducir sus literales sin un follow-up dedicado.
 */
trait RespondsJson
{
    protected $message = [
        // response success ..0
        [
            'message' => 'Response Successfully!',
            'alert-type' => 'success',
        ],
        // created ..1
        [
            'message' => 'Data Created Successfully!',
            'alert-type' => 'success',
        ],
        // data found ..2
        [
            'message' => 'Get Data Successfully!',
            'alert-type' => 'success',
        ],
        //  data updated ..3
        [
            'message' => 'Data Updated Successfully!',
            'alert-type' => 'success',
        ],
        //  validator ..4
        [
            'message' => 'Sorry!!Data validation Failed!',
            'alert-type' => 'error',
        ],
        //  bad request ..5
        [
            'message' => 'Sorry!! You\'ar Send Bad Request!',
            'alert-type' => 'error',
        ],
        //  data not found ..6
        [
            'message' => 'Sorry!! Data Not Found!',
            'alert-type' => 'error',
        ],
        //  data already exist ..7
        [
            'message' => 'Sorry!! Data Already Exists!',
            'alert-type' => 'error',
        ],
        //  unauthorized request ..8
        [
            'message' => 'Sorry!! Unauthenticated Request!',
            'alert-type' => 'error',
        ],
        //  time request error ..9
        [
            'message' => 'Sorry!! Try After Few Time!',
            'alert-type' => 'error',
        ],
        //  check balance error ..10
        [
            'message' => 'Sorry!! Unavailable Balance!',
            'alert-type' => 'error',
        ],
        //  requested accepted ..11
        [
            'message' => 'Data Request Accepted!',
            'alert-type' => 'success',
        ],
        //  requested rejected ..12
        [
            'message' => 'Data Request Rejected!',
            'alert-type' => 'success',
        ],
        //  requested rejected ..13
        [
            'message' => 'Data Not Modified!',
            'alert-type' => 'warning',
        ],
        //  requested rejected ..14
        [
            'message' => 'Data deleted!',
            'alert-type' => 'success',
        ],
    ];

    protected function setSuccess($message)
    {
        session()->flash('type', 'success');
        session()->flash('message', $message);
    }

    protected function setError($message)
    {
        session()->flash('type', 'warning');
        session()->flash('message', $message);
    }

    protected function setDelete($message)
    {
        session()->flash('type', 'warning');
        session()->flash('message', $message);
    }

    protected function respondWithValidatorError($message = '', $data = [], $code = 422)
    {
        return response()->json([
            'error' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function respondWithSuccess($message = '', $data = [], $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function respondWithCreated($message = '', $data = [], $code = 201)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function respondWithFound($message = '', $data = [], $code = 302)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function respondWithUpdate($message = '', $data = [], $code = 426)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function respondWithAccept($message = '', $data = [], $code = 202)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function respondWithReject($message = '', $data = [], $code = 406)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function respondWithNotModify($message = '', $data = [], $code = 304)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function respondWithError($message = '', $data = [], $code = 400)
    {
        return response()->json([
            'error' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function respondWithErrorNotFound($message = '', $data = [], $code = 404)
    {
        return response()->json([
            'error' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function respondWithAlreadyExists($message = '', $data = [], $code = 208)
    {
        return response()->json([
            'error' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function respondWithUnauthorized($message = '', $data = [], $code = 401)
    {
        return response()->json([
            'error' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function respondWithNotAcceptable($message = '', $data = [], $code = 406)
    {
        return response()->json([
            'error' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function respondWithUnavilableBalance($message = '', $data = [], $code = 451)
    {
        return response()->json([
            'error' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }
}
