<?php namespace saleemepoch\PayPal\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

trait PayPalRequest
{
    
    /**
     * @var Client
     */
    private $client;

    /**
     * @var Array
     */
    private $config;

    /**
     * Function To Set PayPal API Configuration
     */
    private function setConfig()
    {
        // Setting Http Client
        $this->client = $this->setClient();

        $paypal = config('paypal');

        // Setting Default PayPal Mode If not set
        if (empty($paypal['mode']) || !in_array($paypal['mode'], ['sandbox', 'live'])) {
            $paypal['mode'] = 'live';
        }

        $mode = $paypal['mode'];

        // Getting PayPal API Credentials
        foreach ($paypal[$mode] as $key=>$value) {
            $this->config[$key] = $value;
        }

        // Setting API Endpoints
        if ($paypal['mode'] == 'sandbox') {
            $this->config['api_url'] = !empty($this->config['secret']) ?
                'https://api-3t.sandbox.paypal.com/nvp' : 'https://api.sandbox.paypal.com/nvp';

            if (!isset($this->config['gateway_url']))
                $this->config['gateway_url'] = 'https://www.sandbox.paypal.com';

        } else {
            $this->config['api_url'] = !empty($this->config['secret']) ?
                'https://api-3t.paypal.com/nvp' : 'https://api.paypal.com/nvp';

            if (!isset($this->config['gateway_url']))
                $this->config['gateway_url'] = 'https://www.paypal.com';

        }

        // Adding params outside sandbox / live array
        $this->config['payment_action'] = $paypal['payment_action'];
        $this->config['currency'] = $paypal['currency'];
        $this->config['notify_url'] = $paypal['notify_url'];

        unset($paypal);
    }

    /**
     * Function to Guzzle Client class object
     *
     * @return Client
     */
    protected function setClient()
    {
        return new Client;
    }

    /**
     * Verify PayPal IPN Response
     *
     * @param $post
     * @return array
     */
    public function verifyIPN($post)
    {
        $response = $this->doPayPalRequest('verifyipn', $post);

        return $response;
    }

    /**
     * Refund PayPal Transaction
     *
     * @param $transaction
     * @return array
     */
    public function refundTransaction($transaction)
    {
        $post = [
            'TRANSACTIONID' =>  $transaction
        ];

        $response = $this->doPayPalRequest('RefundTransaction',$post);

        return $response;
    }

    /**
     * Search Transactions On PayPal
     *
     * @param array $post
     * @return array
     */
    public function searchTransactions($post)
    {
        $response = $this->doPayPalRequest('TransactionSearch', $post);

        return $response;
    }

    /**
     * Function To Perform PayPal API Request
     *
     * @param $method
     * @param $params
     * @return array
     */
    private function doPayPalRequest($method, $params)
    {
        if (empty($this->config))
            self::setConfig();

        // Setting API Credentials, Version & Method
        $post = [
            'USER'      => $this->config['username'],
            'PWD'       => $this->config['password'],
            'SIGNATURE' => $this->config['secret'],
            'VERSION'   => 123,
            'METHOD'    => $method,
        ];

        // Checking Whether The Request Is PayPal IPN Response
        if ($method == 'verifyipn') {
            unset($post['method']);

            $post_url = $this->config['gateway_url'].'/cgi-bin/webscr';
        } else {
            $post_url = $this->config['api_url'];
        }

        foreach ($params as $key=>$value) {
            $post[$key] = $value;
        }

        try {
            $request = $this->client->post($post_url, [
                'form_params' => $post
            ]);

            $response = $request->getBody(true);
            $response = $this->retrieveData($response);

            if ($method == 'SetExpressCheckout') {
                if (!empty($response['TOKEN'])) {
                    $response['paypal_link'] = $this->config['gateway_url'] .
                        '/webscr?cmd=_express-checkout&token=' . $response['TOKEN'];
                } else {
                    return [
                        'type'      => 'error',
                        'message'   => trans('paypal::error.paypal_connection_error')
                    ];
                }
            }

            return $response;

        } catch (ClientException $e) {
            $message = $e->getRequest() . " " . $e->getResponse();
        } catch (ServerException $e) {
            $message = $e->getRequest(). " " . $e->getResponse();
        } catch (BadResponseException $e) {
            $message = $e->getRequest(). " " . $e->getResponse();
        } catch (\Exception $e) {
            $message = $e->getRequest(). " " . $e->getResponse();
        }

        return [
            'type'      => 'error',
            'message'   => $message
        ];
    }

    /**
     * Parse PayPal NVP Response
     *
     * @param $string
     * @return array
     */
    private function retrieveData($string)
    {
        $response = array();
        parse_str($string, $response);
        return $response;
    }

}
