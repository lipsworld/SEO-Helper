<?php namespace Arcanedev\SeoHelper\Tests\Entities;

use Arcanedev\SeoHelper\Contracts\Renderable;
use Arcanedev\SeoHelper\Entities\MiscTags;
use Arcanedev\SeoHelper\Tests\TestCase;

/**
 * Class     MiscTagsTest
 *
 * @package  Arcanedev\SeoHelper\Tests\Entities
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class MiscTagsTest extends TestCase
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /** @var  \Arcanedev\SeoHelper\Contracts\Entities\MiscTags */
    private $misc;

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    public function setUp()
    {
        parent::setUp();

        $config     = $this->getMiscConfig();
        $this->misc = new MiscTags($config);

        $this->misc->setUrl($this->baseUrl);
    }

    public function tearDown()
    {
        unset($this->misc);

        parent::tearDown();
    }

    /* -----------------------------------------------------------------
     |  Tests
     | -----------------------------------------------------------------
     */

    /** @test */
    public function it_can_be_instantiated()
    {
        $expectations = [
            \Arcanedev\SeoHelper\Contracts\Entities\MiscTags::class,
            \Arcanedev\SeoHelper\Contracts\Renderable::class,
            \Arcanedev\SeoHelper\Entities\MiscTags::class,
        ];

        foreach ($expectations as $expected) {
            static::assertInstanceOf($expected, $this->misc);
        }
    }

    /** @test */
    public function it_can_render_canonical()
    {
        $this->misc->setUrl($url = 'http://laravel.com');

        $expected = '<link rel="canonical" href="'.$url.'">';

        static::assertContains($expected, $this->misc->render());
        static::assertContains($expected, (string) $this->misc);

        $this->misc = new MiscTags(['canonical' => false]);

        static::assertEmpty($this->misc->render());
        static::assertEmpty((string) $this->misc);
    }

    /** @test */
    public function it_can_render_robots()
    {
        $expected = '<meta name="robots" content="noindex, nofollow">';

        static::assertContains($expected, $this->misc->render());
        static::assertContains($expected, (string) $this->misc);

        $this->misc = new MiscTags(['robots' => false]);

        static::assertEmpty($this->misc->render());
        static::assertEmpty((string) $this->misc);
    }

    /** @test */
    public function it_can_render_links()
    {
        $author    = 'https://plus.google.com/+AuthorProfile';
        $publisher = 'https://plus.google.com/+PublisherProfile';

        $this->misc = new MiscTags([
            'default' => compact('author', 'publisher')
        ]);

        $expectations = [
            '<link rel="author" href="'.$author.'">',
            '<link rel="publisher" href="'.$publisher.'">',
        ];

        foreach ($expectations as $expected) {
            static::assertContains($expected, $this->misc->render());
            static::assertContains($expected, (string) $this->misc);
        }
    }

    /** @test */
    public function it_can_render()
    {
        $robots    = '<meta name="robots" content="noindex, nofollow">';
        $canonical = '<link rel="canonical" href="'.$this->baseUrl.'">';
        $viewport  = '<meta name="viewport" content="width=device-width, initial-scale=1">';

        $output = $this->misc->render();

        foreach (compact('robots', 'canonical', 'viewport') as $expected) {
            static::assertContains($expected, $output);
        }

        $author    = 'https://plus.google.com/+AuthorProfile';
        $publisher = 'https://plus.google.com/+PublisherProfile';

        $this->misc = new MiscTags(array_merge(
            $this->getMiscConfig(),
            ['default' => compact('author', 'publisher')]
        ));

        static::assertSame(implode(PHP_EOL, [
            $robots,
            '<link rel="author" href="'.$author.'">',
            '<link rel="publisher" href="'.$publisher.'">',
        ]), $this->misc->render());

        $this->misc->setUrl($this->baseUrl);

        static::assertSame(implode(PHP_EOL, [
            $robots,
            '<link rel="author" href="'.$author.'">',
            '<link rel="publisher" href="'.$publisher.'">',
            '<link rel="canonical" href="'.$this->baseUrl.'">',
        ]), $this->misc->render());
    }

    /** @test */
    public function it_can_add_remove_and_reset_tags()
    {
        static::assertNotEmpty($this->misc->render());

        $robots    = '<meta name="robots" content="noindex, nofollow">';
        $canonical = '<link rel="canonical" href="'.$this->baseUrl.'">';
        $viewport  = '<meta name="viewport" content="width=device-width, initial-scale=1">';

        $output = $this->misc->render();

        foreach (compact('robots', 'canonical', 'viewport') as $expected) {
            static::assertContains($expected, $output);
        }

        $this->misc->remove('robots');

        static::assertNotContains($robots, $this->misc->render());

        $this->misc->remove('canonical');

        static::assertNotContains($canonical, $this->misc->render());

        $this->misc->remove('viewport');

        static::assertEmpty($this->misc->render());

        $this->misc->add('document-rating', 'Safe For Work');

        static::assertSame(
            '<meta name="document-rating" content="Safe For Work">',
            $this->misc->render()
        );

        $this->misc->remove('document-rating');

        static::assertEmpty($this->misc->render());

        $this->misc->addMany([
            'copyright' => 'ARCANEDEV',
            'expires'   => 'never',
        ]);

        $output = $this->misc->render();

        static::assertContains('<meta name="copyright" content="ARCANEDEV">', $output);
        static::assertContains('<meta name="expires" content="never">', $output);

        $this->misc->remove(['copyright', 'expires']);

        static::assertEmpty($this->misc->render());

        $this->misc->addMany([
            'document-rating' => 'Safe For Work',
            'copyright'       => 'ARCANEDEV',
            'expires'         => 'never',
        ]);

        static::assertNotEmpty($this->misc->render());

        $this->misc->reset();

        static::assertEmpty($this->misc->render());
    }

    /** @test */
    public function it_can_make()
    {
        $this->misc = MiscTags::make([
            'copyright'       => 'ARCANEDEV',
            'document-rating' => 'Safe For Work',
            'expires'         => 'expires',
        ]);

        $copyright    = '<meta name="copyright" content="ARCANEDEV">';
        $expectations = [
            $copyright,
            '<meta name="document-rating" content="Safe For Work">',
            '<meta name="expires" content="expires">',
        ];

        foreach ($expectations as $expected) {
            static::assertContains($expected, $this->misc->render());
            static::assertContains($expected, (string) $this->misc);
        }

        $this->misc->remove(['expires', 'document-rating']);

        static::assertSame($copyright, $this->misc->render());
        static::assertSame($copyright, (string) $this->misc);
    }

    /* -----------------------------------------------------------------
     |  Other Methods
     | -----------------------------------------------------------------
     */

    /**
     * Get misc config.
     *
     * @return array
     */
    private function getMiscConfig()
    {
        return $this->getSeoHelperConfig('misc', []);
    }
}
