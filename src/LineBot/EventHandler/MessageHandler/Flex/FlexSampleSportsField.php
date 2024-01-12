<?php

declare(strict_types=1);

namespace Lynk\LineBot\EventHandler\MessageHandler\Flex;

use LINE\Clients\MessagingApi\Model\FlexBox;
use LINE\Clients\MessagingApi\Model\FlexBubble;
use LINE\Clients\MessagingApi\Model\FlexComponent;
use LINE\Clients\MessagingApi\Model\FlexImage;
use LINE\Clients\MessagingApi\Model\FlexMessage;
use LINE\Clients\MessagingApi\Model\FlexText;
use LINE\Clients\MessagingApi\Model\MessageAction;
use LINE\Clients\MessagingApi\Model\PostbackAction;
use LINE\Constants\ActionType;
use LINE\Constants\Flex\BubbleContainerSize;
use LINE\Constants\Flex\ComponentAlign;
use LINE\Constants\Flex\ComponentFontSize;
use LINE\Constants\Flex\ComponentFontWeight;
use LINE\Constants\Flex\ComponentImageAspectMode;
use LINE\Constants\Flex\ComponentImageSize;
use LINE\Constants\Flex\ComponentLayout;
use LINE\Constants\Flex\ComponentSpacing;
use LINE\Constants\Flex\ComponentType;
use LINE\Constants\Flex\ContainerType;
use LINE\Constants\MessageType;
use Lynk\LineBot\Model\SportsFieldInfo;

class FlexSampleSportsField
{
    /**
     * @param SportsFieldInfo[] $sportsFieldInfoList
     * @return FlexMessage
     */
    public static function get(array $sportsFieldInfoList): FlexMessage
    {
        return new FlexMessage([
            'type' => MessageType::FLEX,
            'altText' => '距離您最近的球場',
            'contents' => [
                'type' => ContainerType::CAROUSEL,
                'contents' => self::createBubbles($sportsFieldInfoList),
            ]
        ]);
    }

    /**
     * @param SportsFieldInfo[] $sportsFieldInfoList
     * @return FlexBubble[]
     */
    private static function createBubbles(array $sportsFieldInfoList): array
    {
        $bubbles = [];

        foreach ($sportsFieldInfoList as $sportsFieldInfo) {
            array_push(
                $bubbles,
                new FlexBubble([
                    'type' => ContainerType::BUBBLE,
                    'size' => BubbleContainerSize::MICRO,
                    'hero' => self::createHeroBlock($sportsFieldInfo),
                    'body' => self::createBodyBlock($sportsFieldInfo),
                    'action' => new PostbackAction([
                        'type' => ActionType::POSTBACK,
                        'label' => 'action',
                        'data' => 'GymID=' . $sportsFieldInfo->GymID,
                        'displayText' => $sportsFieldInfo->Name
                    ]),
                ])
            );
        }

        return $bubbles;
    }

    /**
     * @param SportsFieldInfo $sportsFieldInfo
     * @return FlexComponent
     */
    private static function createHeroBlock($sportsFieldInfo): FlexComponent
    {
        return new FlexImage([
            'type' => ComponentType::IMAGE,
            'url' => $sportsFieldInfo->Photo1,
            'size' => ComponentImageSize::FULL,
            'aspectRatio' => '320:213',
            'aspectMode' => ComponentImageAspectMode::COVER,
        ]);
    }

    /**
     * @param SportsFieldInfo $sportsFieldInfo
     * @return FlexBox
     */
    private static function createBodyBlock($sportsFieldInfo): FlexBox
    {
        return new FlexBox([
            'type' => ComponentType::BOX,
            'layout' => ComponentLayout::VERTICAL,
            'spacing' => ComponentSpacing::SM,
            'paddingAll' => '13px',
            'contents' => [
                new FlexText([
                    'type' => ComponentType::TEXT,
                    'text' => $sportsFieldInfo->Name,
                    'weight' => ComponentFontWeight::BOLD,
                    'size' => ComponentFontSize::SM,
                    'align' => ComponentAlign::CENTER,
                    'wrap' => true,
                ]),
                new FlexText([
                    'type' => ComponentType::TEXT,
                    'text' => '距離: 約' . $sportsFieldInfo->Distance . '公里',
                    'weight' => ComponentFontWeight::BOLD,
                    'size' => '12px',
                    'align' => ComponentAlign::CENTER,
                ]),
                new FlexBox([
                    'type' => ComponentType::BOX,
                    'layout' => ComponentLayout::VERTICAL,
                    'contents' => [
                        new FlexBox([
                            'type' => ComponentType::BOX,
                            'layout' => ComponentLayout::BASELINE,
                            'spacing' => ComponentSpacing::SM,
                            'contents' => [
                                new FlexText([
                                    'type' => 'text',
                                    'text' => '📍' . $sportsFieldInfo->Address,
                                    'size' => ComponentFontSize::SM,
                                    'flex' => 5,
                                    'warp' => true,
                                    'color' => '#8c8c8c'
                                ])
                            ]
                        ])
                    ]
                ])
            ],
        ]);
    }
}
