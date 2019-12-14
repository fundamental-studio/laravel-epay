<?php

namespace Fundamental\Epay;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Fundamental\Epay\Exceptions\InvalidAmountException;
use Fundamental\Epay\Exceptions\InvalidInvoiceException;
use Fundamental\Epay\Exceptions\InvalidChecksumException;
use Fundamental\Epay\Exceptions\InvalidCurrencyException;
use Fundamental\Epay\Exceptions\InvalidEasypayResponseException;

class Epay
{
    private $data;

    private $isProduction;
    private $min;
    private $secret;
    private $language;

    private $gatewayUrl = 'https://epay.bg/';
    private $testGatewayUrl = 'https://demo.epay.bg/';

    private $encoded;
    private $checksum;

    private $type;
    private $urls = [
        'ok' => '',
        'cancel' => ''
    ];

    private $idn;

    const AVAILABLE_LANGUAGES = ['BG', 'EN'];
    const AVAILABLE_CURRENCIES = ['BGN', 'USD', 'EUR'];
    const AVAILABLE_TYPES = ['paylogin', 'credit_paydirect'];

    /**
     * Undocumented function
     *
     * @param String $page
     * @param array $data
     * @param String $language
     */
    public function __construct(String $type = 'paylogin', array $data = [], String $language = 'BG')
    {
        $this->isProduction = config('EPAY_PRODUCTION');

        $this->min = config('EPAY_MIN');
        $this->secret = config('EPAY_SECRET');

        if (in_array($type, AVAILABLE_TYPES)) {
            $this->type = $type;
        }

        $this->urls = [
            'ok' => config('EPAY_DEFAULT_URL_OK'),
            'cancel' => config('EPAY_DEFAULT_URL_CANCEL')
        ];

        if (in_array(strtoupper($language), AVAILABLE_LANGUAGES)) {
            $this->language = strtoupper($language);
        }

        if (isset($data['amount'])) {
            $this->setData($data['invoice'], $data['amount'], $data['expiration'], $data['description']);
        }
    }

    /**
     * Undocumented function
     *
     * @param string $invoice
     * @param [type] $amount
     * @param string $expiration
     * @param string $description
     * @return void
     */
    public function setData($invoice = '', $amount, $expiration = '', $description = '')
    {
        $this->validateInvoice($invoice);
        $this->validateAmount($amount);
        $this->validateExpiration($expiration);
        $this->validateDescription($description);

        $this->data = [
            'INVOICE' => ($invoice == false or $invoice == '') ? (sprintf("%.0f", rand() * 100000)) : $invoice,
            'EXP_TIME' => ($expiration == '' or $expiration == null or $expiration == false) ? Carbon::now()->addHours(72)->format('d.m.Y H:i:s') : $expiration,
            'AMOUNT' => $amount,
            'DESCRIPTION' => $description,
            'ENCODING' => 'utf-8'
        ];

        $this->encodeRequestData();
    }

    /**
     * Undocumented function
     *
     * @param [type] $invoice
     * @return void
     */
    public function setInvoice($invoice): void
    {
        $this->validateInvoice($invoice);
        $this->data['INVOICE'] = $invoice;
    }

    /**
     * Undocumented function
     *
     * @return String
     */
    public function getInvoice(): String
    {
        return $this->data['INVOICE'];
    }

    public function setAmount($amount): void
    {
        $this->validateAmount($amount);
        $this->data['AMOUNT'] = $amount;
    }

    /**
     * Undocumented function
     *
     * @param [type] $amount
     * @return Double
     */
    public function getAmount($amount): Double
    {
        return (double) $this->data['AMOUNT'];
    }

    /**
     * Undocumented function
     *
     * @param [type] $expiration
     * @return void
     */
    public function setExpiration($expiration): void
    {
        $this->validateExpiration($expiration);
        $this->data['EXP_TIME'] = $expiration;
    }

    /**
     * Undocumented function
     *
     * @return String
     */
    public function getExpiration(): String
    {
        return $this->data['EXP_TIME'];
    }

    /**
     * Undocumented function
     *
     * @param [type] $description
     * @return void
     */
    public function setDescription($description): void
    {
        $this->validateDescription($description);
        $this->data['DESCRIPTION'] = $description;
    }

    /**
     * Undocumented function
     *
     * @return String
     */
    public function getDescription(): String
    {
        return $this->data['DESCRIPTION'];
    }

    /**
     * Undocumented function
     *
     * @return String
     */
    public function requestIDNumber(): String
    {
        $client = new Client();
        $res = $client->request('GET', 'https://www.epay.bg/ezp/reg_bill.cgi', [
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

        throw new Exception();
    }

    /**
     * Undocumented function
     *
     * @return String
     */
    public function getEasypayIDN(): String
    {
        return $this->idn;
    }

    /**
     * Undocumented function
     *
     * @param array $data
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
                        // Expired or other status
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
     * Undocumented function
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
     * Undocumented function
     *
     * @return void
     */
    private function encodeRequestData()
    {
        $this->encoded = base64_encode($this->formatDataArray($this->data));
    }

    /**
     * Undocumented function
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
     * Undocumented function
     *
     * @return String
     */
    public function getEncoded(): String
    {
        return $this->encoded;
    }

    /**
     * Undocumented function
     *
     * @return String
     */
    public function getChecksum(): String
    {
        return $this->checksum;
    }

    /**
     * Undocumented function
     *
     * @return String
     */
    public function getTargetUrl(): String
    {
        return ($this->isProduction) ? $this->gatewayUrl : $this->testGatewayUrl . (($this->language == 'EN') ? 'en/' : '');
    }

    /**
     * Undocumented function
     *
     * @return String
     */
    public function generatePaymentFields(): String
    {
        return `
            <input type="hidden" name="PAGE" value="{$this->type}">
            <input type="hidden" name="LANG" value="{$this->language}">

            <input type="hidden" name="ENCODED" value="{$this->encoded}">
            <input type="hidden" name="CHECKSUM" value="{$this->checksum}">
            <input type="hidden" name="URL_OK" value="{$this->urls['ok']}">
            <input type="hidden" name="URL_CANCEL" value="{$this->urls['cancel']}">
        `;
    }

    /**
     * Undocumented function
     *
     * @param string $id
     * @return String
     */
    public function generatePaymentForm($id = ''): String
    {
        return `
            <form id="{$id}" action="{$this->getTargetUrl()}" method="post">
                {$this->generatePaymentFields()}
            </form>
        `;
    }

    /**
     * Undocumented function
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

    private function validateDescription($description)
    {
        if (strlen($description) > 100) {
            throw new InvalidDescriptionException();
        }
    }

    private function validateCurrency($currency)
    {
        if (!in_array($currency, AVAILABLE_CURRENCIES)) {
            throw new InvalidCurrencyException();
        }
    }
}