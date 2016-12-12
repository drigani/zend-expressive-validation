<?php

namespace ExpressiveValidator\Validator;

use Psr\Http\Message\ServerRequestInterface;
use Zend\I18n\Translator\TranslatorInterface;

interface ValidationRulesInterface
{
    /**
     * @param ServerRequestInterface $request
     * @param TranslatorInterface $translator
     */
    public function __construct(ServerRequestInterface $request, TranslatorInterface $translator = null);

    /**
     * Should return a class mapping
     * of the validations
     * @return  [] mixed
     */
    public function getValidationRules();

    /**
     * Should return the error messages
     * @return [] mixed
     */
    public function getMessages();
}
