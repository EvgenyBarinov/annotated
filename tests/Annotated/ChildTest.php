<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Cycle\Annotated\Tests;

use Cycle\Annotated\Embeddings;
use Cycle\Annotated\Entities;
use Cycle\Annotated\MergeColumns;
use Cycle\Annotated\MergeIndexes;
use Cycle\Annotated\Tests\Fixtures\Child;
use Cycle\Annotated\Tests\Fixtures\Simple;
use Cycle\Annotated\Tests\Fixtures\Third;
use Cycle\ORM\Relation;
use Cycle\ORM\Schema;
use Cycle\Schema\Compiler;
use Cycle\Schema\Generator\GenerateRelations;
use Cycle\Schema\Generator\GenerateTypecast;
use Cycle\Schema\Generator\RenderRelations;
use Cycle\Schema\Generator\RenderTables;
use Cycle\Schema\Generator\ResetTables;
use Cycle\Schema\Generator\SyncTables;
use Cycle\Schema\Registry;
use Doctrine\Common\Annotations\AnnotationReader;
use Spiral\Tokenizer\Config\TokenizerConfig;
use Spiral\Tokenizer\Tokenizer;

abstract class ChildTest extends BaseTest
{
    public function testSimpleSchema(): void
    {
        $r = new Registry($this->dbal);
        (new Entities($this->locator, new AnnotationReader()))->run($r);

        $this->assertTrue($r->hasEntity(Simple::class));
        $this->assertTrue($r->hasEntity('simple'));

        $this->assertTrue($r->getEntity('simple')->getFields()->has('id'));

        $this->assertTrue($r->getEntity('simple')->getFields()->has('name'));
        $this->assertTrue($r->getEntity('simple')->getFields()->has('email'));

        $schema = (new Compiler())->compile($r);

        $this->assertSame([Schema::ROLE => 'simple'], $schema[Child::class]);
        $this->assertSame([Schema::ROLE => 'simple'], $schema[Third::class]);
    }

    public function testRelationToChild(): void
    {
        $tokenizer = new Tokenizer(new TokenizerConfig([
            'directories' => [__DIR__ . '/Fixtures8'],
            'exclude'     => [],
        ]));

        $locator = $tokenizer->classLocator();

        $r = new Registry($this->dbal);

        $schema = (new Compiler())->compile($r, [
            new Embeddings($locator),
            new Entities($locator),
            new ResetTables(),
            new MergeColumns(),
            new GenerateRelations(),
            new RenderTables(),
            new RenderRelations(),
            new MergeIndexes(),
            new SyncTables(),
            new GenerateTypecast(),
        ]);

        $this->assertArrayHasKey('article', $schema['some'][Schema::RELATIONS]);
        $this->assertSame(Relation::HAS_ONE, $schema['some'][Schema::RELATIONS]['article'][Relation::TYPE]);

        $this->assertSame('post', $schema['some'][Schema::RELATIONS]['article'][Relation::TARGET]);
    }
}
