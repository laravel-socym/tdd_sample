<?php

namespace Tests\Feature;

use App\Customer;
use App\Report;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

class CustomerTest extends TestCase
{

    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'TestDataSeeder']);

    }

    /**
     * @test
     */
    public function api_customers_customer_idにDELETEメソッドで訪問記録がある顧客の場合()
    {
        $customer_id = $this->getFirstCustomerId();
        $response = $this->delete('api/customers/' . $customer_id);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @test
     */
    public function api_customers_customer_idにDELETEメソッドで訪問記録がない顧客が削除できる()
    {
        $customer_id = $this->getFirstCustomerId();
        // レポートを削除
        Report::query()->where('customer_id', '=', $customer_id)->delete();
        $this->delete('api/customers/' . $customer_id);
        $this->assertDatabaseMissing('customers', ['id' => $customer_id]);
    }

    /**
     * @test
     */
    public function api_customers_customer_idに存在しないcustomer_idでPUTメソッドでアクセスすると404が返却される()
    {
        $response = $this->putJson('api/customers/999', ['name' => 'name']);
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /**
     * @test
     */
    public function api_customers_customer_idにPUTメソッドで顧客名が編集できる()
    {
        $customer_id = $this->getFirstCustomerId();
        $response = $this->get('api/customers/' . $customer_id);
        $customer = $response->json();
        $new_name = $customer['name'] . '_new';
        $this->putJson('api/customers/' . $customer_id, ['name' => $new_name]);

        $response = $this->get('api/customers/' . $customer_id);
        $customer = $response->json();
        $this->assertSame($new_name, $customer['name']);
    }

    /**
     * @test
     */
    public function api_customers_customer_idに存在しないcustomer_idでGETメソッドでアクセスすると404が返却される()
    {
        $response = $this->get('api/customers/999');
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /**
     * @test
     */
    public function api_customers_customer_idにGETメソッドでアクセスすると顧客情報が返却される()
    {
        $customer_id = $this->getFirstCustomerId();
        $response = $this->get('api/customers/' . $customer_id);
        $this->assertSame(['id', 'name'], array_keys($response->json()));
    }

    /**
     * @test
     */
    public function POST_api_customersのエラーレスポンスの確認()
    {
        $params = ['name' => ''];
        $response = $this->postJson('api/customers', $params);
        $error_response = [
            'message' => "The given data was invalid.",
            'errors' => [
                'name' => [
                    'name は必須項目です'
                ],
            ]
        ];
        $response->assertExactJson($error_response);
    }

    /**
     * @test
     */
    public function POST_api_customersにnameが空の場合422UnprocessableEntityが返却される()
    {
        $params = ['name' => ''];
        $response = $this->postJson('api/customers', $params);
        $response->assertStatus(\Illuminate\Http\Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @test
     */
    public function POST_api_customersにnameが含まれない場合422UnprocessableEntityが返却される()
    {
        $params = [];
        $response = $this->postJson('api/customers', $params);
        $response->assertStatus(\Illuminate\Http\Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @test
     */
    public function api_customersに顧客名をPOSTするとcustomersテーブルにそのデータが追加される()
    {
        $params = [
            'name' => '顧客名',
        ];
        $this->postJson('api/customers', $params);
        $this->assertDatabaseHas('customers', $params);
    }

    /**
     * @test
     */
    public function api_customersにGETメソッドでアクセスすると2件の顧客リストが返却される()
    {
        $response = $this->get('api/customers');
        $response->assertJsonCount(2);
    }

    /**
     * @test
     */
    public function api_customersにGETメソッドで取得できる顧客リストのJSON形式は要件通りである()
    {
        $response = $this->get('api/customers');
        $customers = $response->json();
        $customer = $customers[0];
        $this->assertSame(['id', 'name'], array_keys($customer));
    }

    /**
     * @test
     */
    public function api_customersにGETメソッドでアクセスするとJSONが返却される()
    {
        $response = $this->get('api/customers');
        $this->assertThat($response->content(), $this->isJson());
    }

    /**
     * @test
     */
    public function api_customersにGETメソッドでアクセスできる()
    {
        // 実行部分を記述
        $response = $this->get('api/customers');
        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function api_customersにPOSTメソッドでアクセスできる()
    {
        $customer = [
            'name' => 'customer_name',
        ];
        $response = $this->postJson('api/customers', $customer);
        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function api_customers_customer_idにGETメソッドでアクセスできる()
    {
        $customer_id = $this->getFirstCustomerId();
        $response = $this->get('api/customers/' . $customer_id);
        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function api_customers_customer_idにPUTメソッドでアクセスできる()
    {
        $customer_id = $this->getFirstCustomerId();
        $response = $this->putJson('api/customers/' . $customer_id, ['name' => 'name']);
        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function api_customers_customer_idにDELETEメソッドでアクセスできる()
    {
        $customer_id = $this->getFirstCustomerId();
        // レポートを削除
        Report::query()->where('customer_id', '=', $customer_id)->delete();
        $response = $this->delete('api/customers/' . $customer_id);
        $response->assertStatus(200);
    }

    private function getFirstCustomerId()
    {
        return Customer::query()->first()->value('id');
    }
}
