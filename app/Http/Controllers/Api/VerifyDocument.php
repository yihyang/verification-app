<?php

namespace App\Http\Controllers\Api;

use App\Actions\VerifyDocumentAction;
use App\Http\Controllers\Controller;
use App\Models\Verification;
use OpenApi\Annotations as OA;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Info(
 *     title="Verify Document API",
 *     version="0.1"
 * )
 */
class VerifyDocument extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/verify_document",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 allOf={
     *                     @OA\Schema(
     *                         @OA\Property(
     *                             description="Document",
     *                             property="document",
     *                             type="string", format="binary"
     *                         )
     *                     )
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="ok",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="data",
     *                         type="array",
     *                         description="The response data",
     *                         @OA\Items(
     *                             @OA\Property(
     *                                 property="issuer",
     *                                 type="string",
     *                                 description="Issuer of the file"
     *                             ),
     *                             @OA\Property(
     *                                 property="result",
     *                                 type="string",
     *                                 description="Result of the verification"
     *                             ),
     *                         ),
     *                     ),
     *                     example={
     *                         "data": {
     *                             "issuer": "Legal Issuer",
     *                             "result": "verified"
     *                         }
     *                     }
     *                 )
     *             )
     *         }
     *     )
     * )
     */
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
