<?php

namespace Fundamental\Epay;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Fundamental\Epay\Exceptions\InvalidAmountException;
use Fundamental\Epay\Exceptions\InvalidInvoiceException;
use Fundamental\Epay\Exceptions\InvalidChecksumException;
use Fundamental\Epay\Exceptions\InvalidCurrencyException;
use Fundamental\Epay\Exceptions\InvalidExpirationException;
use Fundamental\Epay\Exceptions\InvalidEasypayResponseException;

class Epay
{
    private $data;

    private $isProduction;
    private $min;
    private $secret;
    private $language = 'BG';

    private $gatewayUrl = 'https://epay.bg/';
    private $testGatewayUrl = 'https://demo.epay.bg/';

    private $easypayGatewayUrl = 'https://www.epay.bg/ezp/reg_bill.cgi';
    private $easypayTestGatewayUrl = 'https://demo.epay.bg/ezp/reg_bill.cgi';

    private $encoded;
    private $checksum;

    private $idn;

    private $type;
    private $urls = [
        'ok' => '',
        'cancel' => ''
    ];

    const AVAILABLE_LANGUAGES = ['BG', 'EN'];
    const AVAILABLE_CURRENCIES = ['BGN', 'USD', 'EUR'];
    const AVAILABLE_TYPES = ['paylogin', 'credit_paydirect'];

    /**
     * Constructing the Epay class instance.
     *
     * @param String $type Can be either paylogin or credit_paydirect.
     * @param array $data May be ommitted and use the setData function.
     * @param String $language Can be either BG or EN.
     */
    public function __construct(String $type = 'paylogin', array $data = [], String $language = 'BG')
    {
        $this->isProduction = config('production');

        $this->min = config('min');
        $this->secret = config('secret');

        if (in_array($type, $this::AVAILABLE_TYPES)) {
            $this->type = $type;
        }

        $this->urls = [
            'ok' => config('urlOk'),
            'cancel' => config('urlCancel')
        ];

        if (in_array(strtoupper($language), $this::AVAILABLE_LANGUAGES)) {
            $this->language = strtoupper($language);
        }

        if (isset($data['amount'])) {
            $this->setData($data['invoice'], $data['amount'], $data['expiration'], $data['description']);
        }
    }

    /**
     * Setting main data for creating and sending the request.
     *
     * @param String $invoice
     * @param double|float|String $amount The amount
     * @param String $expiration
     * @param String $description Invoice description content in less than 100 symbols.
     * @param string $currency
     * @param String $encoding
     * @return void
     */
    public function setData($invoice = false, $amount, $expiration = false, String $description = '', $currency = 'BGN', $encoding = null)
    {
        $this->validateInvoice($invoice);
        $this->validateAmount($amount);
        $this->validateExpiration($expiration);
        $this->validateDescription($description);

        $this->data = [
            'MIN'           => $this->min,
            'INVOICE'       => ($invoice == false and config('generateInvoice')) ? (sprintf("%.0f", rand() * 100000)) : $invoice,
            'EXP_TIME'      => ($expiration == false) ? Carbon::now()->addHours(config('expirationPeriod'))->format('d.m.Y H:i:s') : $expiration,
            'AMOUNT'        => $amount,
            'DESCRIPTION'   => $description
        ];

        if (in_array($currency, $this::AVAILABLE_CURRENCIES)) {
            $this->data['CURRENCY'] = $currency;
        }

        if ($encoding === 'utf-8') {
            $this->data['ENCODING'] = $encoding;
        }

        $this->encodeRequestData();
    }

    /**
     * Setter for invoice number.
     *
     * @param String $invoice The invoice number.
     * @return void
     */
    public function setInvoice($invoice): void
    {
        $this->validateInvoice($invoice);
        $this->data['INVOICE'] = $invoice;
    }

    /**
     * Get the set or generated invoice number.
     *
     * @return String
     */
    public function getInvoice(): String
    {
        return $this->data['INVOICE'];
    }

    /**
     * Setter for amount number.
     *
     * @param double|float|String $amount The invoice amount.
     * @return void
     */
    public function setAmount($amount): void
    {
        $this->validateAmount($amount);
        $this->data['AMOUNT'] = $amount;
    }

    /**
     * Get the invoice amount.
     *
     * @return Double
     */
    public function getAmount(): Double
    {
        return (double) $this->data['AMOUNT'];
    }

    /**
     * Setter for expiration date in format d.m.Y H:i:s
     *
     * @param String $expiration Date format: d.m.Y H:i:s
     * @return void
     */
    public function setExpiration($expiration): void
    {
        $this->validateExpiration($expiration);
        $this->data['EXP_TIME'] = $expiration;
    }

    /**
     * Get the already set expiration time.
     *
     * @return String
     */
    public function getExpiration(): String
    {
        return $this->data['EXP_TIME'];
    }

    /**
     * Setter for invoice description parameter.
     *
     * @param String $description Length should be less than 100 symbols.
     * @return void
     */
    public function setDescription($description): void
    {
        $this->validateDescription($description);
        $this->data['DESCRIPTION'] = $description;
    }

    /**
     * Get the already set description parameter.
     *
     * @return String
     */
    public function getDescription(): String
    {
        return $this->data['DESCRIPTION'];
    }

    /**
     * Send request to the ePay platform for 10 digit code generation and retrieve
     *
     * @return String
     */
    public function requestIDNumber(): String
    {
        $client = new Client();
        $res = $client->request('GET', (($this->isProduction) ? $this->easypayGatewayUrl : $this->easypayTestGatewayUrl), [
            'query' => [
                'ENCODED' => $this->encoded,
                'CHECKSUM' => $this->generateChecksum()
            ]
        ]);

        if ($res->getStatusCode() == 200)
        {
            $idn = (String) $res->getBody();
            $idn = explode('IDN=', $idn);
            $this->idn = trim($idn);

            return $this->idn;
        }

        throw new InvalidEasypayResponseException();
    }

    /**
     * Retrieve the requested and generated IDN for in place payment at EasyPay
     *
     * @return String
     */
    public function getEasypayIDN(): String
    {
        return $this->idn;
    }

    /**
     * Parse result and get all status and ePay generated fields as array.
     *
     * @param array $data Should include encoded and checksum members of the array.
     * @return array
     */
    public static function parseResult(array $data): array
    {
        $encoded = $data['encoded'];
        $checksum = $data['checksum'];
        $hmac = $this->generateChecksum($encoded);

        $result = [];

        if ($hmac == $checksum)
        {
            $data = base64_decode($encoded);
            $lines = explode("\n", $data);

            foreach ($lines as $line)
            {
                if (preg_match("/^INVOICE=(\d+):STATUS=(PAID|DENIED|EXPIRED)(:PAY_TIME=(\d+):STAN=(\d+):BCODE=([0-9a-zA-Z]+))?$/", $line, $regs))
                {
                    $result['invoice']  = $invoice = $regs[1];
                    $result['status']   = $regs[2];

                    $result['pay_date'] = (isset($regs[4])) ? $regs[4] : '';
                    $result['stan']     = (isset($regs[5])) ? $regs[5] : '';
                    $result['bcode']    = (isset($regs[6])) ? $regs[6] : '';

                    if ($status == 'PAID')
                    {
                        $result['response'] = "INVOICE=$invoice:STATUS=OK\n";
                    }
                    else if ($status == 'DENIED')
                    {
                        $result['response'] = "INVOICE=$invoice:STATUS=ERR\n";
                    }
                    else
                    {
                        $result['response'] = "INVOICE=$invoice:STATUS=NO\n";
                    }
                }
            }

            return $result;
        }
        else
        {
            throw new InvalidChecksumException();
        }
    }

    /**
     * Create the needed data array for sending request
     *
     * @param array $data
     * @return void
     */
    private function formatDataArray(array $data)
    {
        $result = "";

        foreach ($data as $key => $value) {
            $result .= "$key=$value\n";
        }

        return rtrim($result, "\n");
    }

    /**
     * Encode the needed and formatted data.
     *
     * @return void
     */
    private function encodeRequestData()
    {
        $this->encoded = base64_encode($this->formatDataArray($this->data));
    }

    /**
     * Generate the checksum of the send or already initialized data array.
     *
     * @param boolean $data
     * @return void
     */
    public function generateChecksum($data = false)
    {
        $data = ($data == false) ? $this->encoded : $data;

        return hash_hmac('sha1', $data, $this->secret);
    }

    /**
     * Get the encoded data string.
     *
     * @return String
     */
    public function getEncoded(): String
    {
        return $this->encoded;
    }

    /**
     * Get the calculated checksum string.
     *
     * @return String
     */
    public function getChecksum(): String
    {
        return $this->checksum;
    }

    /**
     * Get the target url for the ePay platform, using the english version and the test parameter.
     *
     * @return String
     */
    public function getTargetUrl(): String
    {
        return ($this->isProduction) ? $this->gatewayUrl : $this->testGatewayUrl . (($this->language == 'EN') ? 'en/' : '');
    }

    /**
     * Get all hidden input fields for the needed request.
     *
     * @param boolean $urlOk Using the default value from the config or being ommitted.
     * @param boolean $urlCancel Using the default value from the config or being ommitted.
     * @return String All needed hidden input fields
     */
    public function generatePaymentFields($urlOk = false, $urlCancel = false): String
    {
        if ($this->urls['ok'] != false) {
            $this->urls['ok'] = $urlOk;
        }

        if ($this->urls['cancel'] != false) {
            $this->urls['cancel'] = $urlCancel;
        }

        return '
            <input type="hidden" name="PAGE" value="' . $this->type . '">
            <input type="hidden" name="LANG" value="' . $this->language . '">
            <input type="hidden" name="ENCODED" value="' . $this->encoded . '">
            <input type="hidden" name="CHECKSUM" value="' . $this->checksum .'">
            <input type="hidden" name="URL_OK" value="' . $this->urls['ok'] . '">
            <input type="hidden" name="URL_CANCEL" value="' . $this->urls['cancel'] . '">';
    }

    /**
     * Returns a html form with all hidden input fields for the needed request.
     *
     * @param String $id The id element of the generated form.
     * @param boolean $urlOk Using the default value from the config or being ommitted.
     * @param boolean $urlCancel Using the default value from the config or being ommitted.
     * @return String Html form with all hidden fields and set id attribute
     */
    public function generatePaymentForm(String $id = '', $urlOk = false, $urlCancel = false): String
    {
        return '
            <form id="' . $id . '" action="' . $this->getTargetUrl() . '" method="post">
                ' . $this->generatePaymentFields($urlOk, $urlCancel) . '
            </form>';
    }

    /**
     * Get all request parameters for making the ePay request on your own.
     *
     * @return array
     */
    public function getPaymentParameters(): array
    {
        return [
            'URL' => $this->getTargetUrl(),
            'PAGE' => $this->type,
            'LANG' => $this->language,
            'ENCODED' => $this->encoded,
            'CHECKSUM' => $this->checksum,

            'URL_OK' => $this->urls['ok'],
            'URL_CANCEL' => $this->urls['cancel']
        ];
    }

    private function validateInvoice($invoice)
    {
        if (!preg_match('/^\d+$/', (String) $invoice)) {
            throw new InvalidInvoiceException();
        }
    }

    private function validateAmount($amount)
    {
        if (!preg_match('/^\d+(\.(\d+){1,2})?$/', (String) $amount)) {
            throw new InvalidAmountException();
        }
    }

    private function validateExpiration($expiration)
    {
        if (!preg_match('/^[0-9]{2}\.[0-9]{2}\.[0-9]{4}\ [0-9]{2}\:[0-9]{2}\:[0-9]{2}$/', (String) $expiration)) {
            throw new InvalidExpirationException();
        }
    }

    private function validateDescription($description)
    {
        if (strlen($description) > 100) {
            throw new InvalidDescriptionException();
        }
    }

    private function validateCurrency($currency)
    {
        if (!in_array($currency, $this::AVAILABLE_CURRENCIES)) {
            throw new InvalidCurrencyException();
        }
    }
}