<?= "<?php\n" ?>

namespace {{ $gen->entityName() }};

use {{ $gen->entityName() }}\ApiTester;

class Restore{{ $gen->entityName() }}Cest
{
    public function _before(ApiTester $I)
    {
    }

    public function _after(ApiTester $I)
    {
    }

    public function tryToTestRestore{{ $gen->entityName() }}(ApiTester $I)
    {
    }
}