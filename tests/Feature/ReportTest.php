<?php

namespace Tests\Feature;

use App\Customer;
use App\Report;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

class ReportTest extends TestCase
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
    public function api_reports_report_idにDELETEメソッドで訪問記録が削除できる()
    {
        $report_id = $this->getFirstReportId();
        $this->delete('api/reports/' . $report_id);
        $this->assertDatabaseMissing('reports', ['id' => $report_id]);
    }

    /**
     * @test
     */
    public function api_reports_report_idに存在しないreport_idでPUTメソッドでアクセスすると404が返却される()
    {
        $customer_id = $this->getFirstCustomerId();
        $params = [
            'visit_date' => '1973-07-31',
            'customer_id' => $customer_id,
            'detail' => 'detail text',
        ];
        $response = $this->putJson('api/reports/999999999', $params);
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /**
     * @test
     */
    public function api_reports_report_idにPUTメソッドで訪問記録が編集できる()
    {
        $customer_id = $this->getFirstCustomerId();
        $report_id = $this->getFirstReportId();
        $response = $this->get('api/reports/' . $report_id);
        $report = $response->json();
        $new_params = [
            'visit_date' => '1973-07-31',
            'customer_id' => $customer_id + 1,
            'detail' => $report['detail'] . '_new',
        ];
        $this->putJson('api/reports/' . $report_id, $new_params);

        $response = $this->get('api/reports/' . $report_id);
        $report = $response->json();

        $result = array_merge(['id' => $report_id], $new_params);
        $this->assertSame($result, $report);
    }

    /**
     * @test
     */
    public function api_reports_report_idに存在しないreport_idでGETメソッドでアクセスすると404が返却される()
    {
        $response = $this->get('api/reports/999');
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /**
     * @test
     */
    public function api_reports_report_idにGETメソッドでアクセスすると訪問記録が返却される()
    {
        $report_id = $this->getFirstReportId();
        $response = $this->get('api/reports/' . $report_id);
        $this->assertSame(['id', 'visit_date', 'customer_id', 'detail'], array_keys($response->json()));
    }

    /**
     * @test
     */
    public function POST_api_reportsに存在しないcustomer_idがPOSTされた場合422UnprocessableEntityが返却される()
    {
        $params = [
            'visit_date' => '2018-07-31',
            'customer_id' => 999999999999,
            'detail' => 'detail text'
        ];
        $response = $this->postJson('api/reports', $params);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @test
     * @dataProvider getInvalidDateParamsForPostReports
     * @param $params
     */
    public function POST_api_reportsにvisit_dateが不正な日付の場合422UnprocessableEntityが返却される($params)
    {
        $this->replaceCustomerId($params);
        $response = $this->postJson('api/reports', $params);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function getInvalidDateParamsForPostReports()
    {
        $customer_id = 1;
        return [
            [
                ['visit_date' => 'aaa', 'customer_id' => $customer_id, 'detail' => 'detail text'],
            ],
            [
                ['visit_date' => '2018-02-29', 'customer_id' => $customer_id, 'detail' => 'detail text'],
            ],
        ];
    }

    private function replaceCustomerId(&$params)
    {
        $customer_id = $this->getFirstCustomerId();
        foreach ($params as $key => $param) {
            if (array_get($param, 'customer_id')) {
                $params[$key]['customer_id'] = $customer_id;
            }
        }
    }

    /**
     * @test
     * @dataProvider getInvalidParamsForPostReports
     * @param $params
     */
    public function POST_api_reportsに必須データがない場合422UnprocessableEntityが返却される($params)
    {
        $this->replaceCustomerId($params);
        $response = $this->postJson('api/reports', $params);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function getInvalidParamsForPostReports()
    {
        $customer_id = 1;
        return [
            [
                ['visit_date' => '', 'customer_id' => '', 'detail' => ''],
            ],
            [
                ['visit_date' => '2018-07-31', 'customer_id' => '', 'detail' => ''],
            ],
            [
                ['visit_date' => '', 'customer_id' => $customer_id, 'detail' => ''],
            ],
            [
                ['visit_date' => '', 'customer_id' => '', 'detail' => 'detail text'],
            ],
            [
                ['visit_date' => '2018-07-31', 'customer_id' => $customer_id, 'detail' => ''],
            ],
            [
                ['visit_date' => '', 'customer_id' => $customer_id, 'detail' => 'detail text'],
            ],
            [
                ['visit_date' => '2018-07-31', 'customer_id' => '', 'detail' => 'detail text'],
            ],
        ];
    }

    /**
     * @test
     */
    public function api_reportsにPOSTするとreportsテーブルにそのデータが追加される()
    {
        $customer_id = $this->getFirstCustomerId();
        $params = [
            'visit_date' => '2018-07-31',
            'customer_id' => $customer_id,
            'detail' => 'detail text',
        ];
        $this->postJson('api/reports', $params);
        $this->assertDatabaseHas('reports', $params);
    }

    /**
     * @test
     */
    public function api_reportsにGETメソッドでアクセスすると4件の訪問記録が返却される()
    {
        $response = $this->get('api/reports');
        $response->assertJsonCount(4);
    }

    /**
     * @test
     */
    public function api_reportsにGETメソッドで取得できる顧客リストのJSON形式は要件通りである()
    {
        $response = $this->get('api/reports');
        $reports = $response->json();
        $report = $reports[0];
        $this->assertSame(['id', 'visit_date', 'customer_id', 'detail'], array_keys($report));
    }

    /**
     * @test
     */
    public function api_reportsにGETメソッドでアクセスするとJSONが返却される()
    {
        $response = $this->get('api/reports');
        $this->assertThat($response->content(), $this->isJson());
    }

    /**
     * @test
     */
    public function api_reportsにGETメソッドでアクセスできる()
    {
        $response = $this->get('api/reports');
        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function api_reportsにPOSTメソッドでアクセスできる()
    {
        $customer_id = $this->getFirstCustomerId();
        $params = [
            'visit_date' => '2018-07-31',
            'customer_id' => $customer_id,
            'detail' => 'detail text',
        ];
        $response = $this->postJson('api/reports', $params);
        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function api_reports_report_idにGETメソッドでアクセスできる()
    {
        $report_id = $this->getFirstReportId();
        $response = $this->get('api/reports/' . $report_id);
        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function api_reports_report_idにPUTメソッドでアクセスできる()
    {
        $report_id = $this->getFirstReportId();
        $customer_id = $this->getFirstCustomerId();
        $params = [
            'visit_date' => '2018-07-31',
            'customer_id' => $customer_id,
            'detail' => 'detail text',
        ];
        $response = $this->putJson('api/reports/' . $report_id, $params);
        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function api_reports_report_idにDELETEメソッドでアクセスできる()
    {
        $response = $this->delete('api/reports/1');
        $response->assertStatus(200);
    }

    private function getFirstCustomerId()
    {
        return Customer::query()->first()->value('id');
    }

    private function getFirstReportId()
    {
        return Report::query()->first()->value('id');
    }

}
