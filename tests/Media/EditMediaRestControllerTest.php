<?php

namespace CultuurNet\UDB3\Symfony\Media;

use CultuurNet\UDB3\Media\ImageUploaderInterface;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EditMediaRestControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ImageUploaderInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $imageUploader;

    /**
     * @var EditMediaRestController
     */
    protected $controller;

    public function setUp()
    {
        $this->imageUploader = $this->createMock(ImageUploaderInterface::class);
        $this->controller = new EditMediaRestController($this->imageUploader);
    }

    /**
     * @test
     * @dataProvider incompleteUploadRequestsProvider
     */
    public function it_should_return_an_error_response_when_media_meta_data_is_missing_for_an_upload(
        Request $uploadRequest,
        Response $expectedErrorResponse
    ) {
        $response = $this->controller->upload($uploadRequest);

        $this->assertEquals($expectedErrorResponse->getContent(), $response->getContent());
    }

    public function incompleteUploadRequestsProvider()
    {
        return [
            'missing description' => [
                'request' => new Request(
                    [],
                    [
                        'copyrightHolder' => 'Danny',
                        'language' => 'nl',
                    ],
                    [],
                    [],
                    [
                        'file' => $this->createMock(UploadedFile::class),
                    ]
                ),
                'response' => new JsonResponse(['error' => 'description required'], 400),
            ],
            'missing language' => [
                'request' => new Request(
                    [],
                    [
                        'copyrightHolder' => ' Dwight Hooker',
                        'description' => 'Lenna'
                    ],
                    [],
                    [],
                    [
                        'file' => $this->createMock(UploadedFile::class),
                    ]
                ),
                'response' => new JsonResponse(['error' => 'language required'], 400),
            ],
            'copyright holder language' => [
                'request' => new Request(
                    [],
                    [
                        'description' => 'Lenna',
                        'language' => 'nl',
                    ],
                    [],
                    [],
                    [
                        'file' => $this->createMock(UploadedFile::class),
                    ]
                ),
                'response' => new JsonResponse(['error' => 'copyright holder required'], 400),
            ],
            'file' => [
                'request' => new Request(
                    [],
                    [
                        'copyrightHolder' => ' Dwight Hooker',
                        'description' => 'Lenna',
                        'language' => 'nl',
                    ]
                ),
                'response' => new JsonResponse(['error' => 'file required'], 400),
            ],
        ];
    }

    /**
     * @test
     */
    public function it_should_pass_along_upload_data_to_the_image_uploader_create_a_job_and_return_a_command_id()
    {
        $uploadRequest = new Request(
            [],
            [
                'description' => 'Lenna',
                'copyrightHolder' => ' Dwight Hooker',
                'language' => 'nl',
            ],
            [],
            [],
            [
                'file' => $this->createMock(UploadedFile::class),
            ]
        );

        $this->imageUploader
            ->expects($this->once())
            ->method('upload')
            ->willReturn('9691CCF6-7D9F-499F-A97C-4E50ACB8BB7E');

        $response = $this->controller->upload($uploadRequest);

        $expectedResponseContent = json_encode([
            'commandId' => '9691CCF6-7D9F-499F-A97C-4E50ACB8BB7E'
        ]);

        $this->assertEquals($expectedResponseContent, $response->getContent());
    }
}
