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
Exchange plugin DI container
"""

# pylint: disable=no-value-for-parameter

# Library dependencies
from kink import di
from whistle import EventDispatcher

# Library libs
from exchange_plugin.publisher import Publisher


def create_container() -> None:
    """Create exchange plugin services"""
    di["exchange-plugin_event-dispatcher"] = EventDispatcher()

    di["exchange-plugin_publisher"] = Publisher()