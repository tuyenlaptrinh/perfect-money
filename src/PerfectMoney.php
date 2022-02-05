<?php

namespace tuyenlaptrinh\PerfectMoney;

use Carbon\Carbon;
use GuzzleHttp\Client;

/**
 * Class PerfectMoney
 */
class PerfectMoney{

    /**
     * @var string
     */
    protected $account_id;

    /**
     * @var string
     */
    protected $passphrase;

    /**
     * @var string
     */
    protected $alt_passphrase;

    /**
     * @var string
     */
    protected $merchant_id;

    /**
     * @var string
     */
    protected $client;

    public function __construct()
    {

        $this->account_id = config('perfectmoney.account_id');
        $this->passphrase = config('perfectmoney.passphrase');
        $this->alt_passphrase = config('perfectmoney.alternate_passphrase');
        $this->merchant_id = config('perfectmoney.merchant_id');
        $this->client = new Client(['verify' => false]);

    }

    protected function sendRequest($url, $data = false){
        $response = $this->client->get($url);
        if($response->getStatusCode() == 200){
            $body = $response->getBody()->getContents();
            if(!preg_match_all("/<input name='(.*)' type='hidden' value='(.*)'>/", $body, $result, PREG_SET_ORDER)){
                return ['status' => 'error', 'message' => 'Invalid output'];
            }
            else{
                return ['status' => 'success', 'result' => $result];
            }

        }
        else{
            return ['status' => 'error', 'message' => 'Connection error'];
        }
    }

    /**
     * get the balance for the wallet
     *
     * @return array
     */

    public function wallets()
    {

        // Get data from the server
        $response = $this->sendRequest('https://perfectmoney.is/acct/balance.asp?AccountID=' . $this->account_id . '&PassPhrase=' . $this->passphrase);

        if($response['status'] == 'success'){
            $data = ['wallets' => []];
            foreach($response['result'] as $item)
            {
                if($item[1] != 'ERROR')
                {
                    $data['wallets'][] = [
                        'account' 	=> $item[1],
                        'balance'	=> $item[2]
                    ];
                }
            }

            $data['status'] = 'success';
            return $data;
        }

        return $response;

    }

    /**
     * Send Money
     *
     * @param   string        $account
     * @param   double        $amount
     * @param   string        $descripion
     * @param   string        $payment_id
     *
     * @return array
     */
    public function sendMoney($account, $amount, $descripion = '', $payment_id = '')
    {

        // trying to open URL to process PerfectMoney Spend request
        $dataQuery = array(
            'AccountID' => trim($this->account_id),
            'PassPhrase' => trim($this->passphrase),
            'Payer_Account' => trim($this->merchant_id),
            'Payee_Account' => trim($account),
            'Amount' => $amount,
        );
        if(!empty($descripion)){
            $dataQuery['Memo'] = trim($descripion);
        }
        if(!empty($payment_id)){
            $dataQuery['PAYMENT_ID'] = trim($payment_id);
        }

        $response = $this->sendRequest('https://perfectmoney.is/acct/confirm.asp?'.http_build_query($dataQuery));

        if($response['status'] == 'success'){
            $data = [];
            foreach($response['result'] as $item)
            {
                if($item[1] != 'ERROR')
                {
                    $data['data'][$item[1]] = $item[2];
                }
            }
            $data['status'] = 'success';
            return $data;
        }

        return $response;
    }

    /**
     * Render form
     *
     * @param array $data
     *
     */
    public static function render(array $data = [])
    {

        $view_data = [
            'PAYEE_ACCOUNT'			=> ($data['PAYEE_ACCOUNT'] ?? config('perfectmoney.marchant_id')),
            'PAYEE_NAME'			=> ($data['PAYEE_NAME'] ?? config('perfectmoney.marchant_name')),
            'PAYMENT_AMOUNT'		=> ($data['PAYMENT_AMOUNT'] ?? ''),
            'PAYMENT_UNITS'			=> ($data['PAYMENT_UNITS'] ?? config('perfectmoney.units')),
            'PAYMENT_ID'			=> ($data['PAYMENT_ID'] ?? null),
            'PAYMENT_URL'			=> ($data['PAYMENT_URL'] ?? config('perfectmoney.payment_url')),
            'NOPAYMENT_URL'			=> ($data['NOPAYMENT_URL'] ?? config('perfectmoney.nopayment_url')),
        ];

        $html = '<input type="hidden" name="PAYEE_ACCOUNT" value="'.$view_data['PAYEE_ACCOUNT'].'">';
        $html .= '<input type="hidden" name="PAYEE_NAME" value="'.$view_data['PAYEE_NAME'].'">';
        $html .= '<input type="hidden" name="PAYMENT_AMOUNT" value="'.$view_data['PAYMENT_AMOUNT'].'">';
        $html .= '<input type="hidden" name="PAYMENT_UNITS" value="'.$view_data['PAYMENT_UNITS'].'">';
        $html .= '<input type="hidden" name="PAYMENT_URL" value="'.$view_data['PAYMENT_URL'].'">';
        $html .= '<input type="hidden" name="NOPAYMENT_URL" value="'.$view_data['NOPAYMENT_URL'].'">';
        if(!empty($view_data['PAYMENT_ID'])){
            $html .= '<input type="hidden" name="PAYMENT_ID" value="'.$view_data['PAYMENT_ID'].'">';
        }

        // Status URL
        $view_data['STATUS_URL'] = null;
        if(config('perfectmoney.status_url') || isset( $data['STATUS_URL'] ))
        {
            $html .= '<input type="hidden" name="STATUS_URL" value="'.($data['STATUS_URL'] ?? config('perfectmoney.status_url')).'">';
        }

        // Payment URL Method
        $view_data['PAYMENT_URL_METHOD'] = null;
        if(config('perfectmoney.payment_url_method') || isset($data['PAYMENT_URL_METHOD']))
        {
            $view_data['PAYMENT_URL_METHOD'] = ($data['PAYMENT_URL_METHOD'] ?? config('perfectmoney.payment_url_method'));
            $html .= '<input type="hidden" name="PAYMENT_URL_METHOD" value="'.$view_data['PAYMENT_URL_METHOD'].'">';
        }

        $view_data['NOPAYMENT_URL_METHOD'] = null;
        if(config('perfectmoney.nopayment_url_method') || isset($data['NOPAYMENT_URL_METHOD']))
        {
            $view_data['NOPAYMENT_URL_METHOD'] = ($data['NOPAYMENT_URL_METHOD'] ?? config('perfectmoney.nopayment_url_method'));
            $html .= '<input type="hidden" name="NOPAYMENT_URL_METHOD" value="'.$view_data['NOPAYMENT_URL_METHOD'].'">';
        }

        // Memo
        $view_data['MEMO'] = null;
        if(config('perfectmoney.suggested_memo') || isset($data['SUGGESTED_MEMO']))
        {
            $html .= '<input type="hidden" name="SUGGESTED_MEMO" value="'.($data['SUGGESTED_MEMO'] ?? config('perfectmoney.suggested_memo')).'">';

        }
        return $html;
    }


    /**
     * This script demonstrates querying account history
     * using PerfectMoney API interface.
     *
     * @param   int        $start_day
     * @param   int        $start_month
     * @param   int        $end_year
     * @param   int        $end_day
     * @param   int        $end_month
     * @param   int        $end_year
     *
     * @return array
     */
    public function histories($start_day = null, $start_month = null, $start_year = null, $end_day = null, $end_month = null, $end_year = null, $data = [])
    {

        $start_day = ($start_day ?: Carbon::now()->subDays(29)->day);
        $start_month = ($start_month ?: Carbon::now()->subDays(29)->month);
        $start_year =  ($start_year ?: Carbon::now()->subDays(29)->year);
        $end_day = ($end_day ?: Carbon::now()->day);
        $end_month = ($end_month ?: Carbon::now()->month);
        $end_year = ($end_year ?: Carbon::now()->year);
        $dataQuery = array(
            'startmonth' => $start_month,
            'startday' => $start_day,
            'startyear' => $start_year,
            'endmonth' => $end_month,
            'endday' => $end_day,
            'endyear' => $end_year,
            'AccountID' => trim($this->account_id),
            'PassPhrase' => trim($this->passphrase)
        );

        $dataQuery['payment_id'] = $data['payment_id'] ?? '';
        $dataQuery['batchfilter'] = $data['batchfilter'] ?? '';
        if(isset($data['counterfilter'])) {
            $dataQuery['counterfilter'] = $data['counterfilter'];
        }
        $dataQuery['metalfilter'] = $data['metalfilter'] ?? '';
        $dataQuery['oldsort'] = isset($data['oldsort']) && in_array(strtolower($data['oldsort']), ['tstamp', 'batch_num', 'metal_name', 'counteraccount_id', 'amount '])  ? $data['oldsort'] : '';
        $dataQuery['paymentsmade'] = isset($data['paymentsmade']) && $data['paymentsmade'] == true ? 1 : 0;
        $dataQuery['paymentsreceived'] = isset($data['paymentsreceived']) && $data['paymentsreceived'] == true ? 1 : 0;
        $url = 'https://perfectmoney.is/acct/historycsv.asp?'.http_build_query($dataQuery);
        $response = $this->client->get($url);
        if($response->getStatusCode() == 200){
            $body = $response->getBody()->getContents();
            if (substr($body, 0, 63) == 'Time,Type,Batch,Currency,Amount,Fee,Payer Account,Payee Account') {
                $lines = explode("\n", $url);
                $rows = explode(",", $lines[0]);
                $data = [];
                $data['histories'] = [];
                for($i=1; $i < count($lines); $i++){
                    if(empty($lines[$i]))
                    {
                        break;
                    }
                    $items = explode(',', $lines[$i]);
                    $history_line = [];
                    foreach($items as $key => $value)
                    {
                        $history_line[str_replace(' ', '_', strtolower($rows[$key]))] = $value;
                    }
                    $data['histories'][] = $history_line;

                }

                $data['status'] = 'success';

                return $data;
            }
            return ['status' => 'error', 'message' => $body];
        }
        return ['status' => 'error', 'message' => 'Connection error'];
    }

    public function hash()
    {
        $string = \request()->input('PAYMENT_ID') . ':';
        $string .= \request()->input('PAYEE_ACCOUNT') . ':';
        $string .= \request()->input('PAYMENT_AMOUNT') . ':';
        $string .= \request()->input('PAYMENT_UNITS') . ':';
        $string .= \request()->input('PAYMENT_BATCH_NUM') . ':';
        $string .= \request()->input('PAYER_ACCOUNT') . ':';
        $string .= strtoupper(md5($this->alt_passphrase)) . ':';
        $string .= \request()->input('TIMESTAMPGMT');
        return strtoupper(md5($string));
    }

    public function verified()
    {
        $hash = $this->hash();
        if($hash == \request()->input('V2_HASH')){
            return true;
        }
        else return false;
    }
}
