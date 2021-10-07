#!/usr/bin/python3

#     Copyright 2021. FastyBird s.r.o.
#
#     Licensed under the Apache License, Version 2.0 (the "License");
#     you may not use this file except in compliance with the License.
#     You may obtain a copy of the License at
#
#         http://www.apache.org/licenses/LICENSE-2.0
#
#     Unless required by applicable law or agreed to in writing, software
#     distributed under the License is distributed on an "AS IS" BASIS,
#     WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
#     See the License for the specific language governing permissions and
#     limitations under the License.

"""
Messages publisher proxy
"""

# Library dependencies
from abc import ABC
from typing import List, Dict, Set
from kink import inject
from modules_metadata.routing import RoutingKey
from modules_metadata.types import ModuleOrigin
from whistle import EventDispatcher

from exchange_plugin.events.messages import MessagePublished


class IPublisher(ABC):
    """
    Exchange publisher interface

    @package        FastyBird:ExchangePlugin!
    @module         publisher

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """
    def publish(
        self,
        origin: ModuleOrigin,
        routing_key: RoutingKey,
        data: Dict,
    ) -> None:
        """Publish data to exchange bus"""


@inject
class Publisher:
    """
    Event fired by triggers handler when trigger property action is fired

    @package        FastyBird:ExchangePlugin!
    @module         publisher

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """
    __publishers: Set[IPublisher]
    __event_dispatcher: EventDispatcher

    # -----------------------------------------------------------------------------

    def __init__(
        self,
        publishers: List[IPublisher],
        event_dispatcher: EventDispatcher,
    ) -> None:
        self.__publishers = set(publishers)
        self.__event_dispatcher = event_dispatcher

    # -----------------------------------------------------------------------------

    def publish(
        self,
        origin: ModuleOrigin,
        routing_key: RoutingKey,
        data: Dict,
    ) -> None:
        """Call all registered publishers and publish data"""
        for publisher in self.__publishers:
            publisher.publish(origin=origin, routing_key=routing_key, data=data)

        self.__event_dispatcher.dispatch(
            MessagePublished.EVENT_NAME,
            MessagePublished(
                origin=origin,
                routing_key=routing_key,
                data=data,
            )
        )

    # -----------------------------------------------------------------------------

    def register_publisher(
        self,
        publisher: IPublisher,
    ) -> None:
        """Register new publisher to proxy"""
        self.__publishers.add(publisher)