<?php

namespace App\Http\Controllers;

use App\Actions\DeletePushSubscriptionAction;
use App\Actions\UpdatePushSubscriptionAction;
use App\Http\Requests\DeletePushSubscriptionRequest;
use App\Http\Requests\StorePushSubscriptionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class PushSubscriptionController extends Controller
{
    public function store(
        StorePushSubscriptionRequest $request,
        UpdatePushSubscriptionAction $action,
    ): JsonResponse {
        $action->execute($request->user(), $request->validated());

        return response()->json(['enabled' => true]);
    }

    public function destroy(
        DeletePushSubscriptionRequest $request,
        DeletePushSubscriptionAction $action,
    ): Response {
        $action->execute($request->user(), $request->validated('endpoint'));

        return response()->noContent();
    }
}
