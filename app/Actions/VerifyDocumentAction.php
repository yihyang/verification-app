<?php
namespace App\Actions;

class VerifyDocumentAction
{
    // TODO: to further improve this
    // matches the following:
    // - test-12@gmail
    // - test_123@gmail.com
    // - test@subdomain.gmail.com
    // does not match :
    // - test@gmail.
    const EMAIL_REGEX = "/^([\w\+_\-]+)(\.[\w\+_\-]+)*@([\w\-]+\.)+[\w]+$/ix";

    // RESULTS
    const RESULT_VERIFIED = 'verified';
    const RESULT_INVALID_RECIPIENT = 'invalid_recipient';
    const RESULT_INVALID_ISSUER = 'invalid_issuer';
    const RESULT_INVALID_SIGNATURE = 'invalid_signature';

    // issuers
    const ACCEPTED_ISSUER_PROOF_TYPES = ['DNS-DID'];
    const ISSUER_IDENTITY_PROOF_LOCATION_REGEX = "/^\w+(\.[\w]+)+$/";

    // signature
    const ACCEPTED_SIGNATURE_TYPES = ['SHA3MerkleProof'];

    private array $document;

    public function __construct(array $document)
    {
        $this->document = $document;
    }

    public function verify()
    {
        $data = data_get($this->document, 'data');

        if (!$data) {
            return self::RESULT_INVALID_RECIPIENT;
        }

        /* recipient */
        $recipient = data_get($data, 'recipient');
        if (!$recipient) {
            return self::RESULT_INVALID_RECIPIENT;
        }

        if (!$this->verifyValidRecipient($recipient)) {
            return self::RESULT_INVALID_RECIPIENT;
        }

        /* issuer */
        $issuer = data_get($data, 'issuer');
        if (!$issuer) {
            return self::RESULT_INVALID_ISSUER;
        }
        // verify valid issuer
        if (!$this->verifyValidIssuer($issuer)) {
            return self::RESULT_INVALID_ISSUER;
        }

        /* signature */
        if (!$this->verifyValidSignature($this->document)) {
            return self::RESULT_INVALID_SIGNATURE;
        }

        return self::RESULT_VERIFIED;
    }

    private function verifyValidRecipient(array $recipient)
    {
        $name = data_get($recipient, 'name');
        $email = data_get($recipient, 'email');

        if (!$name || !$email) {
            return false;
        }

        if (!preg_match(self::EMAIL_REGEX, $email)) {
            return false;
        }

        return true;
    }

    private function verifyValidIssuer(array $issuer) : bool
    {
        $name = data_get($issuer, 'name');
        $identityProofType = data_get($issuer, 'identityProof.type');
        $identityProofKey = data_get($issuer, 'identityProof.key');
        $identityProofLocation = data_get($issuer, 'identityProof.location');

        if (!$name || !$identityProofType || !$identityProofKey || !$identityProofLocation) {
            return false;
        }

        if (!in_array($identityProofType, self::ACCEPTED_ISSUER_PROOF_TYPES)) {
            return false;
        }

        if (!preg_match(self::ISSUER_IDENTITY_PROOF_LOCATION_REGEX, $identityProofLocation)) {
            return false;
        }

        $isValidDNS = (new VerifyDNSAction($identityProofType, $identityProofLocation, $identityProofKey))->verify();

        if (!$isValidDNS) {
            return false;
        }

        return true;
    }

    private function verifyValidSignature(array $document) : bool
    {
        $signatureType = data_get($document, 'signature.type');
        $signatureTargetHash = data_get($document, 'signature.targetHash');

        if (!$signatureType || !$signatureTargetHash) {
            return false;
        }

        if (!in_array($signatureType, self::ACCEPTED_SIGNATURE_TYPES)) {
            return false;
        }

        $generatedSignature = (new ConvertNestedArrayToSignatureAction($document['data']))->convert();

        return $generatedSignature == $signatureTargetHash;
    }
}
