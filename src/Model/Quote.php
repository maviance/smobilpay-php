<?php
/**
 * Quote
 *
 * PHP version 5
 *
 * @category Class
 * @package  Maviance\S3PApiClient
 * @author   Swagger Codegen team
 * @link     https://github.com/swagger-api/swagger-codegen
 */

/**
 * Smobilpay S3P API
 *
 * Smobilpay Third Party API FOR PAYMENT COLLECTIONS
 *
 * OpenAPI spec version: 3.0.2
 * 
 * Generated by: https://github.com/swagger-api/swagger-codegen.git
 * Swagger Codegen version: 3.0.24
 */
/**
 * NOTE: This class is auto generated by the swagger code generator program.
 * https://github.com/swagger-api/swagger-codegen
 * Do not edit the class manually.
 */

namespace Maviance\S3PApiClient\Model;

use \ArrayAccess;
use \Maviance\S3PApiClient\ObjectSerializer;

/**
 * Quote Class Doc Comment
 *
 * @category Class
 * @package  Maviance\S3PApiClient
 * @author   Swagger Codegen team
 * @link     https://github.com/swagger-api/swagger-codegen
 */
class Quote implements ModelInterface, ArrayAccess
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $swaggerModelName = 'Quote';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $swaggerTypes = [
        'quoteId' => 'string',
'expiresAt' => '\DateTime',
'payItemId' => 'string',
'amountLocalCur' => 'float',
'priceLocalCur' => 'float',
'priceSystemCur' => 'float',
'localCur' => 'string',
'systemCur' => 'string',
'promotion' => 'string'    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $swaggerFormats = [
        'quoteId' => null,
'expiresAt' => 'date',
'payItemId' => null,
'amountLocalCur' => 'float',
'priceLocalCur' => 'float',
'priceSystemCur' => 'float',
'localCur' => null,
'systemCur' => 'decimal',
'promotion' => null    ];

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function swaggerTypes()
    {
        return self::$swaggerTypes;
    }

    /**
     * Array of property to format mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function swaggerFormats()
    {
        return self::$swaggerFormats;
    }

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @var string[]
     */
    protected static $attributeMap = [
        'quoteId' => 'quoteId',
'expiresAt' => 'expiresAt',
'payItemId' => 'payItemId',
'amountLocalCur' => 'amountLocalCur',
'priceLocalCur' => 'priceLocalCur',
'priceSystemCur' => 'priceSystemCur',
'localCur' => 'localCur',
'systemCur' => 'systemCur',
'promotion' => 'promotion'    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'quoteId' => 'setQuoteId',
'expiresAt' => 'setExpiresAt',
'payItemId' => 'setPayItemId',
'amountLocalCur' => 'setAmountLocalCur',
'priceLocalCur' => 'setPriceLocalCur',
'priceSystemCur' => 'setPriceSystemCur',
'localCur' => 'setLocalCur',
'systemCur' => 'setSystemCur',
'promotion' => 'setPromotion'    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'quoteId' => 'getQuoteId',
'expiresAt' => 'getExpiresAt',
'payItemId' => 'getPayItemId',
'amountLocalCur' => 'getAmountLocalCur',
'priceLocalCur' => 'getPriceLocalCur',
'priceSystemCur' => 'getPriceSystemCur',
'localCur' => 'getLocalCur',
'systemCur' => 'getSystemCur',
'promotion' => 'getPromotion'    ];

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @return array
     */
    public static function attributeMap()
    {
        return self::$attributeMap;
    }

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @return array
     */
    public static function setters()
    {
        return self::$setters;
    }

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @return array
     */
    public static function getters()
    {
        return self::$getters;
    }

    /**
     * The original name of the model.
     *
     * @return string
     */
    public function getModelName()
    {
        return self::$swaggerModelName;
    }

    

    /**
     * Associative array for storing property values
     *
     * @var mixed[]
     */
    protected $container = [];

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values
     *                      initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->container['quoteId'] = isset($data['quoteId']) ? $data['quoteId'] : null;
        $this->container['expiresAt'] = isset($data['expiresAt']) ? $data['expiresAt'] : null;
        $this->container['payItemId'] = isset($data['payItemId']) ? $data['payItemId'] : null;
        $this->container['amountLocalCur'] = isset($data['amountLocalCur']) ? $data['amountLocalCur'] : null;
        $this->container['priceLocalCur'] = isset($data['priceLocalCur']) ? $data['priceLocalCur'] : null;
        $this->container['priceSystemCur'] = isset($data['priceSystemCur']) ? $data['priceSystemCur'] : null;
        $this->container['localCur'] = isset($data['localCur']) ? $data['localCur'] : null;
        $this->container['systemCur'] = isset($data['systemCur']) ? $data['systemCur'] : null;
        $this->container['promotion'] = isset($data['promotion']) ? $data['promotion'] : null;
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];

        if ($this->container['quoteId'] === null) {
            $invalidProperties[] = "'quoteId' can't be null";
        }
        if ($this->container['expiresAt'] === null) {
            $invalidProperties[] = "'expiresAt' can't be null";
        }
        if ($this->container['payItemId'] === null) {
            $invalidProperties[] = "'payItemId' can't be null";
        }
        if ($this->container['amountLocalCur'] === null) {
            $invalidProperties[] = "'amountLocalCur' can't be null";
        }
        if ($this->container['priceLocalCur'] === null) {
            $invalidProperties[] = "'priceLocalCur' can't be null";
        }
        if ($this->container['priceSystemCur'] === null) {
            $invalidProperties[] = "'priceSystemCur' can't be null";
        }
        if ($this->container['localCur'] === null) {
            $invalidProperties[] = "'localCur' can't be null";
        }
        if ($this->container['systemCur'] === null) {
            $invalidProperties[] = "'systemCur' can't be null";
        }
        return $invalidProperties;
    }

    /**
     * Validate all the properties in the model
     * return true if all passed
     *
     * @return bool True if all properties are valid
     */
    public function valid()
    {
        return count($this->listInvalidProperties()) === 0;
    }


    /**
     * Gets quoteId
     *
     * @return string
     */
    public function getQuoteId()
    {
        return $this->container['quoteId'];
    }

    /**
     * Sets quoteId
     *
     * @param string $quoteId Unique quote number (UUID)
     *
     * @return $this
     */
    public function setQuoteId($quoteId)
    {
        $this->container['quoteId'] = $quoteId;

        return $this;
    }

    /**
     * Gets expiresAt
     *
     * @return \DateTime
     */
    public function getExpiresAt()
    {
        return $this->container['expiresAt'];
    }

    /**
     * Sets expiresAt
     *
     * @param \DateTime $expiresAt Expiration timestamp. The quote will only stay active the expiration time. (Format: ISO 8601)
     *
     * @return $this
     */
    public function setExpiresAt($expiresAt)
    {
        $this->container['expiresAt'] = $expiresAt;

        return $this;
    }

    /**
     * Gets payItemId
     *
     * @return string
     */
    public function getPayItemId()
    {
        return $this->container['payItemId'];
    }

    /**
     * Sets payItemId
     *
     * @param string $payItemId Unique  Payment Item ID identifying the item to request the quote for
     *
     * @return $this
     */
    public function setPayItemId($payItemId)
    {
        $this->container['payItemId'] = $payItemId;

        return $this;
    }

    /**
     * Gets amountLocalCur
     *
     * @return float
     */
    public function getAmountLocalCur()
    {
        return $this->container['amountLocalCur'];
    }

    /**
     * Sets amountLocalCur
     *
     * @param float $amountLocalCur Service amount in local currency
     *
     * @return $this
     */
    public function setAmountLocalCur($amountLocalCur)
    {
        $this->container['amountLocalCur'] = $amountLocalCur;

        return $this;
    }

    /**
     * Gets priceLocalCur
     *
     * @return float
     */
    public function getPriceLocalCur()
    {
        return $this->container['priceLocalCur'];
    }

    /**
     * Sets priceLocalCur
     *
     * @param float $priceLocalCur Price of payment in local currency
     *
     * @return $this
     */
    public function setPriceLocalCur($priceLocalCur)
    {
        $this->container['priceLocalCur'] = $priceLocalCur;

        return $this;
    }

    /**
     * Gets priceSystemCur
     *
     * @return float
     */
    public function getPriceSystemCur()
    {
        return $this->container['priceSystemCur'];
    }

    /**
     * Sets priceSystemCur
     *
     * @param float $priceSystemCur Price of payment in system currency
     *
     * @return $this
     */
    public function setPriceSystemCur($priceSystemCur)
    {
        $this->container['priceSystemCur'] = $priceSystemCur;

        return $this;
    }

    /**
     * Gets localCur
     *
     * @return string
     */
    public function getLocalCur()
    {
        return $this->container['localCur'];
    }

    /**
     * Sets localCur
     *
     * @param string $localCur Local currency of service. (Format: ISO 4217)
     *
     * @return $this
     */
    public function setLocalCur($localCur)
    {
        $this->container['localCur'] = $localCur;

        return $this;
    }

    /**
     * Gets systemCur
     *
     * @return string
     */
    public function getSystemCur()
    {
        return $this->container['systemCur'];
    }

    /**
     * Sets systemCur
     *
     * @param string $systemCur Currency of billing by  system. (Format: ISO 4217)
     *
     * @return $this
     */
    public function setSystemCur($systemCur)
    {
        $this->container['systemCur'] = $systemCur;

        return $this;
    }

    /**
     * Gets promotion
     *
     * @return string
     */
    public function getPromotion()
    {
        return $this->container['promotion'];
    }

    /**
     * Sets promotion
     *
     * @param string $promotion Optional comma seperated list of current or upcoming promotions offered by the quoted service
     *
     * @return $this
     */
    public function setPromotion($promotion)
    {
        $this->container['promotion'] = $promotion;

        return $this;
    }
    /**
     * Returns true if offset exists. False otherwise.
     *
     * @param integer $offset Offset
     *
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }

    /**
     * Gets offset.
     *
     * @param integer $offset Offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }

    /**
     * Sets value based on offset.
     *
     * @param integer $offset Offset
     * @param mixed   $value  Value to be set
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    /**
     * Unsets offset.
     *
     * @param integer $offset Offset
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }

    /**
     * Gets the string presentation of the object
     *
     * @return string
     */
    public function __toString()
    {
        if (defined('JSON_PRETTY_PRINT')) { // use JSON pretty print
            return json_encode(
                ObjectSerializer::sanitizeForSerialization($this),
                JSON_PRETTY_PRINT
            );
        }

        return json_encode(ObjectSerializer::sanitizeForSerialization($this));
    }
}
