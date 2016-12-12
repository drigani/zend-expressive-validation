<?php
namespace ExpressiveValidator\Validator;

use Psr\Http\Message\ServerRequestInterface;
use Zend\I18n\Translator\TranslatorInterface;

trait ValidationRulesConstructorTrait
{
    /**
     * @var ServerRequestInterface
     */
    private $request;
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    public function __construct(ServerRequestInterface $request, TranslatorInterface $translator = null)
    {
        $this->request = $request;
        $this->translator = $translator;
    }
}
