<?php

/**
 * Created by Dumitru Russu. e-mail: dmitri.russu@gmail.com
 * Date: 7/8/13
 * Time: 8:47 PM
 * Sepa Group Header
 */

namespace SEPA;

use DateTime;
use Exception;
use SimpleXMLElement;

/**
 * Class SepaGroupHeader
 *
 * @package SEPA
 */
class GroupHeader extends Message implements GroupHeaderInterface
{
    const GROUPING = 'MIXD';
    /**
     * Point to point reference assigned by the instructing party and sent to the next party in the chain
     * to unambiguously identify the message.
     * Max35Text
     *
     * @var string
     */
    private string $messageIdentification = '';
    /**
     * Date and time at which a (group of) payment instruction(s) was created by the instructing party.
     *
     * @var
     */
    private $CreationDateTime = '';
    /**
     * Unique and unambiguous way of identifying an organisation
     *
     * @var string
     */
    private $OrganisationIdentification = '';
    /**
     * @var string
     */
    private $Issuer = '';
    /**
     * Unique and unambiguous identification of a person, eg, passport.
     *
     * @var string
     */
    private $PrivateIdentification = '';
    /**
     * Name by which a party is known and which is usually used to identify that party.
     * max length 140
     *
     * @var string
     */
    private $InitiatingPartyName = '';
    //Postal Address
    private $AddressLine = '';
    private $Country = '';
    /**
     * Total of all individual amounts included in the message, irrespective of currencies
     *
     * @var float
     */
    private $ControlSum = 0.00;
    /**
     * Total number of transactions
     * max length 15
     *
     * @var int
     */
    private $NumberOfTransactions = 0;
    /**
     * Identifies whether a single entry per individual transaction or a batch entry for the sum of the amounts of
     * alltransactions within the group of a message is requested.
     *
     * @var string
     */
    private $batchBooking = false;

    /**
     * Group header Mesage Id setter
     * var length max 35;
     *
     * @param $msgId
     * @return $this
     * @throws Exception
     */
    public function setMessageIdentification($msgId): GroupHeader
    {
        $msgId = $this->unicodeDecode($msgId);

        if (!$this->checkStringLength($msgId, 35)) {
            throw new Exception(ERROR_MSG_MESSAGE_IDENTIFICATION);
        }
        $this->messageIdentification = $msgId;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessageIdentification(): string
    {
        return $this->messageIdentification;
    }

    /**
     * Setter for the time of creation of the group header (hence of the sepa message)
     *
     * @param $CreDtTm
     * @return $this
     */
    public function setCreationDateTime($CreDtTm): GroupHeader
    {
        $this->CreationDateTime = $CreDtTm;
        return $this;
    }

    public function getCreationDateTime(): string
    {
        $date = new DateTime();

        if (!$this->CreationDateTime) {
            $this->CreationDateTime = $date->format('Y-m-d\TH:i:s');
        }

        return $this->CreationDateTime;
    }

    /**
     * Unique and unambiguous way of identifying an organisation
     *
     * @param $organisationId
     * @return $this
     */
    public function setOrganisationIdentification($organisationId): GroupHeader
    {
        $this->OrganisationIdentification = $organisationId;
        return $this;
    }

    /**
     * @param $issuer
     * @return $this
     */
    public function setIssuer($issuer): GroupHeader
    {
        $this->Issuer = $issuer;
        return $this;
    }

    /**
     * Setter for the sepa creditor identifier
     *
     * @param $PrvtId
     * @return $this
     */
    public function setPrivateIdentification($PrvtId): GroupHeader
    {
        $this->PrivateIdentification = $PrvtId;
        return $this;
    }

    /**
     * Party that initiates the payment. This can either be the creditor or a party that initiates the
     * direct debit on behalf of the creditor.
     *
     * @param $name
     * @return $this
     * @throws Exception
     */
    public function setInitiatingPartyName($name): GroupHeader
    {
        $name = $this->unicodeDecode($name);

        if (!$this->checkStringLength($name, 140)) {
            throw new Exception(ERROR_MSG_INITIATING_PARTY_NAME);
        }
        $this->InitiatingPartyName = $name;
        return $this;
    }

    /**
     * @throws Exception
     */
    public function setAddressLine($name): GroupHeader
    {
        $name = $this->unicodeDecode($name);

        if (!$this->checkStringLength($name, 140)) {
            throw new Exception(ERROR_MSG_INITIATING_PARTY_NAME);
        }

        $this->AddressLine = $name;

        return $this;
    }

    /**
     * @throws Exception
     */
    public function setCountry($name): GroupHeader
    {
        $name = $this->unicodeDecode($name);

        if (!$this->checkStringLength($name, 140)) {
            throw new Exception(ERROR_MSG_INITIATING_PARTY_NAME);
        }

        $this->Country = $name;

        return $this;
    }

    public function getInitiatingPartyName(): string
    {
        return $this->InitiatingPartyName;
    }

    public function getAddressLine(): string
    {
        return $this->AddressLine;
    }

    public function getCountry(): string
    {
        return $this->Country;
    }

    /**
     * This method returns the total Amount that has been registered for all payment info
     *
     * @param $amount
     * @return GroupHeader
     */
    public function setControlSum($amount)
    {
        $this->ControlSum = $this->sumOfTwoOperands($this->ControlSum, $amount);
        return $this;
    }

    /**
     * Get total number of transactions
     *
     * @param $nbTransactions
     * @return GroupHeader
     */
    public function setNumberOfTransactions($nbTransactions)
    {
        $this->NumberOfTransactions += $nbTransactions;
        return $this;
    }

    /**
     * This method returns the total Amount that has been registered for all payment info
     *
     * @return string
     */
    public function getControlSum(): string
    {
        return $this->amountToString($this->ControlSum);
    }

    /**
     * Get total number of transactions
     *
     * @return int
     */
    public function getNumberOfTransactions(): int
    {
        return $this->NumberOfTransactions;
    }

    /**
     * Identifies whether a single entry per individual transaction or a batch entry for the sum of the amounts of
     * all transactions within the group of a message is requested.
     *
     * @param $value
     * @return $this
     * @throws Exception
     */
    public function setBatchBooking($value): GroupHeader
    {
        if (is_null($value) || empty($value)) {
            throw new Exception(ERROR_MSG_PM_BATCH_BOOKING);
        }

        $this->batchBooking = $value;
        return $this;
    }

    /**
     * @return bool|string
     */
    public function getBatchBooking()
    {
        return $this->batchBooking;
    }

    /**
     * Returns a XML for the group Header object
     *
     * @return SimpleXMLElement
     */
    public function getSimpleXmlGroupHeader(): SimpleXMLElement
    {
        $id = null;
        $groupHeader = new SimpleXMLElement("<GrpHdr></GrpHdr>");
        $groupHeader->addChild('MsgId', $this->getMessageIdentification());
        $groupHeader->addChild('CreDtTm', $this->getCreationDateTime());
        if ($this->getDocumentPainMode() === self::PAIN_001_001_02) {
            $groupHeader->addChild('BtchBookg', $this->boolToString($this->getBatchBooking()));
        }
        $groupHeader->addChild('NbOfTxs', $this->getNumberOfTransactions());
        $groupHeader->addChild('CtrlSum', $this->getControlSum());

        if ($this->getDocumentPainMode() === self::PAIN_001_001_02) {
            $groupHeader->addChild('Grpg', self::GROUPING);
        }

        $initiatingParty = $groupHeader->addChild('InitgPty');
        $initiatingParty->addChild('Nm', $this->getInitiatingPartyName());

        if (!empty($this->OrganisationIdentification) || !empty($this->PrivateIdentification)) {
            $id = $initiatingParty->addChild('Id');
        }

        if (!empty($this->OrganisationIdentification)) {
            $concrete_id = $id->addChild('OrgId');
            $other = $concrete_id->addChild('Othr');
            $other->addChild('Id', $this->OrganisationIdentification);
            if (!empty($this->Issuer)) {
                $other->addChild('Issr', $this->Issuer);
            }
        } elseif (!empty($this->PrivateIdentification)) {
            $concrete_id = $id->addChild('PrvtId');
            $other = $concrete_id->addChild('Othr');
            $other->addChild('Id', $this->PrivateIdentification);
        }

        if ($this->getAddressLine() && $this->getCountry()) {
            $postalAddress = $initiatingParty->addChild('PstlAdr');
            $postalAddress->addChild('AdrLine', $this->getAddressLine());
            $postalAddress->addChild('Ctry', $this->getCountry());
        }

        return $groupHeader;
    }
}
