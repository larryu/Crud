<?= "<?php\n" ?>

namespace App\Containers\{{ $gen->containerName() }}\Tasks\{{ $gen->entityName() }};

use App\Containers\{{ $gen->containerName() }}\Data\Repositories\{{ $repoClass = $gen->entityName().'Repository' }};
use App\Ship\Parents\Tasks\Task;

/**
 * {{ $gen->taskClass('Delete') }} Class.
 */
class {{ $gen->taskClass('Delete') }} extends Task
{
	public function run(int $id) {
        ${{ $camelEntity = camel_case($gen->entityName()) }} = app({{ $repoClass }}::class)->delete($id);
        return ${{ $camelEntity }};
	}
}
