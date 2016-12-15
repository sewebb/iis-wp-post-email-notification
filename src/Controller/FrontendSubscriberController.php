<?php

namespace Nstaeger\WpPostEmailNotification\Controller;

use InvalidArgumentException;
use Nstaeger\CmsPluginFramework\Controller;
use Nstaeger\CmsPluginFramework\Http\Exceptions\HttpBadRequestException;
use Nstaeger\WpPostEmailNotification\Model\SubscriberModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class FrontendSubscriberController extends Controller
{
    public function post(Request $request, SubscriberModel $subscriberModel)
    {
        try {
            $subscriberModel->add($request);
        } catch (InvalidArgumentException $e) {
            throw new HttpBadRequestException($e->getMessage());
        }

        return new JsonResponse();
    }

    public function delete( Request $request, SubscriberModel $subscriberModel ) {
        $subscriber = json_decode($request->getContent());

        if ( ! isset( $subscriber->id ) || empty( $subscriber->id ) ) {
            throw new HttpBadRequestException( "Id x saknas" );
        }

        $id   = intval( $subscriber->id );
        $subscriberModel->external_delete( $id );

		return new JsonResponse();

    }
}
