<?= "<?php\n" ?>

namespace {{ $gen->entityName() }};

use {{ $gen->containerName() }}\ApiTester;
use {{ $gen->entityModelNamespace() }};
use Vinkla\Hashids\Facades\Hashids;

/**
 * Create{{ $gen->entityName() }}Cest Class.
 */
class Create{{ $gen->entityName() }}Cest
{
    private $endpoint = 'v1/{{ str_slug($gen->tableName, $separator = "-") }}/create';

    /**
     * @var App\Containers\User\Models\User
     */
    private $user;

    public function _before(ApiTester $I)
    {
        $this->user = $I->loginAdminUser();
        $I->initData();
        $I->haveHttpHeader('Accept', 'application/json');
    }

    public function _after(ApiTester $I)
    {
    }

    public function tryToTestCreate{{ $gen->entityName() }}(ApiTester $I)
    {
        $data = factory({{ $gen->entityName() }}::class)->make();
@foreach ($fields as $field)
@if(strpos($field->validation_rules, 'confirmed') !== false)
        $data->{{ $field->name }}_confirmation = $data->{{ $field->name }};
@endif
@if($field->namespace)
        $data->{{ $field->name }} = Hashids::encode($data->getAttributes()['{{ $field->name }}']);
@endif
@endforeach

        $I->sendPOST($this->endpoint, $data->getAttributes());

        $I->seeResponseCodeIs(200);

@foreach ($fields as $field)
@if(!$field->hidden && $field->namespace)
        $I->seeResponseContainsJson(['{{ $field->name }}' => $data->getAttributes()['{{ $field->name }}']]);
@elseif(!$field->hidden && $field->name !== "id" && !in_array($field->type, ['timestamp', 'datetime', 'date']))
        $I->seeResponseContainsJson(['{{ $field->name }}' => $data->{{ $field->name }}]);
@endif
@endforeach
    }
}
