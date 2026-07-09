<?php
declare(strict_types=1);

namespace BootstrapUI\Test\TestCase\View\Helper;

use BootstrapUI\View\Helper\FlashHelper;
use Cake\Http\ServerRequest;
use Cake\Http\Session;
use Cake\TestSuite\TestCase;
use Cake\View\View;

/**
 * FlashHelperTest class
 *
 */
class FlashHelperTest extends TestCase
{
    /**
     * @var \Cake\View\View
     */
    public $View;

    /**
     * @var \BootstrapUI\View\Helper\FlashHelper
     */
    public $Flash;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $session = new Session();
        $this->View = new View(new ServerRequest(['session' => $session]));
        $this->Flash = new FlashHelper($this->View);

        $session->write([
            'Flash' => [
                'flash' => [
                    'key' => 'flash',
                    'message' => 'This is a calling',
                    'element' => 'flash/default',
                    'params' => [],
                ],
                'error' => [
                    'key' => 'error',
                    'message' => 'This is error',
                    'element' => 'flash/error',
                    'params' => [],
                ],
                'custom1' => [
                    'key' => 'custom1',
                    'message' => 'This is custom1',
                    'element' => 'flash/warning',
                    'params' => [],
                ],
                'custom2' => [
                    'key' => 'custom2',
                    'message' => 'This is custom2',
                    'element' => 'flash/default',
                    'params' => ['class' => 'foobar'],
                ],
                'custom3' => [
                    'key' => 'custom3',
                    'message' => 'This is <a href="#">custom3</a>',
                    'element' => 'flash/default',
                    'params' => ['escape' => false],
                ],
            ],
        ]);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->View, $this->Flash);
    }

    /**
     * testFlash method
     *
     * @return void
     */
    public function testRender()
    {
        $result = $this->Flash->render('nonExistentKey');
        $this->assertNull($result);

        $result = $this->Flash->render();
        $this->assertStringContainsString('<div role="alert" class="alert alert-dismissible fade in alert-info">', $result);
        $this->assertStringContainsString('<button type="button" class="close" data-dismiss="alert" aria-label="Close">', $result);
        $this->assertStringContainsString('<span aria-hidden="true">&times;</span></button>', $result);
        $this->assertStringContainsString('This is a calling', $result);

        $result = $this->Flash->render('error');
        $this->assertStringContainsString('<div role="alert" class="alert alert-dismissible fade in alert-danger">', $result);
        $this->assertStringContainsString('<button type="button" class="close" data-dismiss="alert" aria-label="Close">', $result);
        $this->assertStringContainsString('This is error', $result);

        $result = $this->Flash->render('custom1', ['params' => ['class' => ['alert']]]);
        $this->assertStringContainsString('<div role="alert" class="alert alert-warning">', $result);
        $this->assertStringNotContainsString('<span aria-hidden="true">&times;</span></button>', $result);
        $this->assertStringContainsString('This is custom1', $result);

        $result = $this->Flash->render('custom2');
        $this->assertStringContainsString('<div role="alert" class="foobar">', $result);
        $this->assertStringContainsString('This is custom2', $result);

        $result = $this->Flash->render('custom3');
        $this->assertStringContainsString('This is <a href="#">custom3</a>', $result);
    }

    /**
     * In CakePHP 3.1 you multple message per key
     *
     * @return void
     */
    public function testRenderForMultipleMessages()
    {
        $this->View->getRequest()->getSession()->write([
            'Flash' => [
                'flash' => [
                    [
                        'key' => 'flash',
                        'message' => 'This is a calling',
                        'element' => 'flash/default',
                        'params' => [],
                    ],
                    [
                        'key' => 'flash',
                        'message' => 'This is a second message',
                        'element' => 'flash/default',
                        'params' => ['class' => ['extra']],
                    ],
                ],
                'error' => [
                    [
                        'key' => 'error',
                        'message' => 'This is error',
                        'element' => 'flash/error',
                        'params' => [],
                    ],
                ],
            ],
        ]);

        $result = $this->Flash->render();
        $this->assertStringContainsString('<div role="alert" class="alert alert-dismissible fade in alert-info">', $result);
        $this->assertStringContainsString('<button type="button" class="close" data-dismiss="alert" aria-label="Close">', $result);
        $this->assertStringContainsString('<span aria-hidden="true">&times;</span></button>', $result);
        $this->assertStringContainsString('This is a calling', $result);

        $this->assertStringContainsString('<div role="alert" class="extra alert-info">', $result);
        $this->assertStringContainsString('This is a second message', $result);

        $result = $this->Flash->render('error');
        $this->assertStringContainsString('<div role="alert" class="alert alert-dismissible fade in alert-danger">', $result);
        $this->assertStringContainsString('<button type="button" class="close" data-dismiss="alert" aria-label="Close">', $result);
        $this->assertStringContainsString('This is error', $result);
    }
}
