<?php

namespace App\Http\Controllers\Api;

use App\Actions\VerifyDocumentAction;
use App\Http\Controllers\Controller;
use App\Models\Verification;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerifyDocument extends Controller
{
    //
    public function __invoke(Request $request)
    {
        // get file
        $document = $request->file('document');
        $document = json_decode(file_get_contents($document), true);

        // invalid document
        if ($document) {
            $result = (new VerifyDocumentAction($document))->verify();
        } else {
            $result = VerifyDocumentAction::RESULT_ERROR;
        }

        // run action
        $issuer = data_get($document, "data.issuer.name");

        // TODO: extract this into action if the logic grows
        // store result
        if ($user = $request->user()) {
            Verification::create([
                'user_id' => $user->id,
                'file_type' => Verification::FILE_TYPE_JSON,
                'result' => $result,
            ]);
        }

        // return result
        return new JsonResponse([
            'data' => [
                'issuer' => $issuer,
                'result' => $result,
            ]
        ]);
    }
}
