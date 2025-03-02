<?php

/**
 * Created by Dumitru Russu. e-mail: dmitri.russu@gmail.com
 * Date: 7/8/13
 * Time: 8:48 PM
 * Sepa Message NameSpace
 */

namespace SEPA;

use Exception;
use SimpleXMLElement;

/**
 * Class SepaMessage
 *
 * @package SEPA
 */
class Message extends XMLGenerator implements MessageInterface
{
    /**
     * @var$groupHeaderObjects GroupHeader
     */
    private $groupHeaderObjects;
    /**
     * @var $message SimpleXMLElement
     */
    private SimpleXMLElement $message;
    /**
     * @var $storeXmlPaymentsInfo SimpleXMLElement
     */
    private SimpleXMLElement $storeXmlPaymentsInfo;
    /**
     * @var array
     */
    private array $paymentInfoObjects = array();

    public function __construct()
    {
        $this->createMessage();
        $this->storeXmlPaymentsInfo = new SimpleXMLElement('<payments></payments>');
        parent::__construct();
    }

    /**
     * @throws Exception
     */
    private function createMessage()
    {
        switch ($this->getDocumentPainMode()) {
            case self::PAIN_001_001_02:
            case self::PAIN_001_001_03:
            {
                $documentMessage = "<CstmrCdtTrfInitn></CstmrCdtTrfInitn>";
                break;
            }
            default:
            {
                $documentMessage = "<CstmrDrctDbtInitn></CstmrDrctDbtInitn>";
                break;
            }
        }

        $this->message = new SimpleXMLElement($documentMessage);
    }

    /**
     * Add Group Header
     *
     * @param GroupHeader $groupHeaderObject
     * @return $this
     */
    public function setMessageGroupHeader(GroupHeader $groupHeaderObject): Message
    {
        if (is_null($this->groupHeaderObjects)) {
            $this->groupHeaderObjects = $groupHeaderObject;
        }

        return $this;
    }

    /**
     * @return GroupHeader
     */
    public function getMessageGroupHeader(): GroupHeader
    {
        return $this->groupHeaderObjects;
    }

    /**
     * Add Message Payment Info
     *
     * @param PaymentInfo $paymentInfoObject
     * @return $this
     * @throws Exception
     */
    public function addMessagePaymentInfo(PaymentInfo $paymentInfoObject): Message
    {
        if (!($paymentInfoObject instanceof PaymentInfo)) {
            throw new Exception('Was not PaymentInfo Object in addMessagePaymentInfo');
        }

        $paymentInfoObject->resetNumberOfTransactions();
        $paymentInfoObject->resetControlSum();
        $this->paymentInfoObjects[$paymentInfoObject->getSequenceType()] = $paymentInfoObject;
        return $this;
    }

    /**
     * Get Payment Info Objects
     *
     * @return array
     */
    public function getPaymentInfoObjects(): array
    {
        return $this->paymentInfoObjects;
    }

    /**
     * Get Simple Xml Element Message
     *
     * @return SimpleXMLElement
     * @throws Exception
     */
    public function getSimpleXMLElementMessage(): SimpleXMLElement
    {
        /**
         * @var $paymentInfo PaymentInfo
         */
        foreach ($this->paymentInfoObjects as $paymentInfo) {
            if (!$paymentInfo->checkIsValidPaymentInfo()) {
                throw new Exception(ERROR_MSG_INVALID_PAYMENT_INFO . $paymentInfo->getPaymentInformationIdentification());
            }

            $paymentInfo->resetControlSum();
            $paymentInfo->resetNumberOfTransactions();

            $this->simpleXmlAppend($this->storeXmlPaymentsInfo, $paymentInfo->getSimpleXMLElementPaymentInfo());

            $this->getMessageGroupHeader()->setNumberOfTransactions($paymentInfo->getNumberOfTransactions());
            $this->getMessageGroupHeader()->setControlSum($paymentInfo->getControlSum());
        }

        $this->simpleXmlAppend($this->message, $this->getMessageGroupHeader()->getSimpleXmlGroupHeader());

        foreach ($this->storeXmlPaymentsInfo->children() as $element) {
            $this->simpleXmlAppend($this->message, $element);
        }

        return $this->message;
    }
}
