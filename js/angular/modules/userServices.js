var userServices = angular.module('userServices', ['ngResource']);

/**
 * Captures and stores certain data properties as messages
 *
 * @function handle(response) : Initialized through UserServices factory
 *
 **/
userServices.factory('ResponseMessage', ['$location',
    function($location){
        var _extend = angular.extend, _each = angular.forEach,
            _self = {}, _locationDefaults = {};

        _self.messages = {};
        _self.handlers = [];
        _self.action = '';


        //  Reset all Messages for the path; @path - defaults to the current $location.$$path
        _self.newMessages = function(path) {
            var _messages = this.getMessages(path);

            _messages.hasMessages = false;

            _each(_messages, function(messagesGroups, type) {
                _each(messagesGroups, function(messages, group) {
                    delete _messages[type][group];
                });
            });

            return _messages;
        }

        //  Check for registered Messages for the path; @path - defaults to the current $location.$$path
        _self.hasMessages = function(path) {
            var _messages = this.getMessages(path);

            _messages.hasMessages = false;

            _each(_messages, function(messagesGroups, type) {
                _each(messagesGroups, function(messages, group) {

                    if(messages.length < 2) return;

                    _messages.hasMessages||(_messages.hasMessages=true);
                });
            });

            return _messages.hasMessages;
        }

        //  Gets the Messages for the path; @path - defaults to the current $location.$$path
        _self.getMessages = function(path) {

            path||(path=$location.$$path);

            var _locationDefaults = ['errors', 'messages', 'prompts'];

            if(!_self.messages.hasOwnProperty(path))  {
                _self.messages[path] = {};
                _each(_locationDefaults, function(type) {
                    _self.messages[path][type] = {};
                })
                _self.messages[path].hasMessages = false;
            }

            return _self.messages[path];
        }

        // Handle a $resource interceptor response
        _self.handle = function(response) {

            //console.log(response.data);
            _self.handleMessages(response.resource);

            _each(_self.handlers, function(handler) {
                handler(response.resource);
            });

            return response.resource;
        }

        _self.handleMessages = function(response, path) {
            //  Get the message object for our current location
            var _messages = _self.newMessages(path);

            //  Check our response for message objects and capture them
            _each(_messages, function(current, type) {
                if(response.hasOwnProperty(type)) {
                    //  Flag the hasMessages property if it's not already true
                    _each(response[type], function(message, messageGroup) {
                        _messages.hasMessages||(_messages.hasMessages=true);

                        if(!_messages[type].hasOwnProperty(messageGroup)) {
                            _messages[type][messageGroup] = [];
                        };

                        _messages[type][messageGroup].push(message);
                    });
                }
            });
        }

        return _self;

    }]);

userServices.factory('UserServices', ['$resource', 'ResponseMessage',
    function($resource, ResponseMessage) {

        var _self = {},
            _actionDefaults = { method: 'POST', interceptor: { response: ResponseMessage.handle } },
            _actions = {};

        //  Application Actions
        _actions.app = angular.extend({}, _actionDefaults, { params:{ action: 'get-user-application' } });

        //  User Info Actions
        _actions.info = angular.extend({}, _actionDefaults, { params:{ action: 'get-user-info' }, cache : true });
        _actions.saveInfo = angular.extend({}, _actionDefaults, { params:{ action: 'save-user-info' } });

        _actions.acceptPrompt = angular.extend({}, _actionDefaults, { params:{ action: 'accept-prompt' } });
        _actions.dismissPrompt = angular.extend({}, _actionDefaults, { params:{ action: 'dismiss-prompt' } });

        //  Campaign Actions
        _actions.campaign = angular.extend({}, _actionDefaults, { params:{ action: 'get-user-campaign', id: '@id' } });
        _actions.addCampaign = angular.extend({}, _actionDefaults, { params:{ action: 'add-user-campaign' } });
        _actions.closeCampaign = angular.extend({}, _actionDefaults, { params:{ action: 'close-user-campaign' } });
        _actions.saveCampaign = angular.extend({}, _actionDefaults, { params:{ action: 'save-user-campaign', user_campaign: '@user_campaign' } });
        _actions.deleteCampaign = angular.extend({}, _actionDefaults, { params:{ action: 'delete-user-campaign', id: '@id' } });
        _actions.campaigns = angular.extend({}, _actionDefaults, { params:{ action: 'get-user-campaigns', 'is_array': true }, isArray: true, cache : true });

        //  Team Actions
        _actions.newTeam = angular.extend({}, _actionDefaults, { params:{ action: 'create-new-team' } });
        _actions.disbandTeam = angular.extend({}, _actionDefaults, { params:{ action: 'disband-team' } });
        _actions.removeFromTeam = angular.extend({}, _actionDefaults, { params:{ action: 'remove-from-team' } });
        _actions.teamToManage = angular.extend({}, _actionDefaults, { params:{ action: 'team-to-manage' } });
        _actions.teamEmailInvites = angular.extend({}, _actionDefaults, { params:{ action: 'team-email-invites' } });
        _actions.promoteUser = angular.extend({}, _actionDefaults, { params:{ action: 'promote-team-member' } });


        _self.server = $resource(home_url+'wp-admin/admin-ajax.php', {}, _actions);
        _self.actionDefaults = _actionDefaults;

        return _self;

    }]);

userServices.factory('App', ['UserServices', 'ResponseMessage',
    function(UserServices, ResponseMessage) {

        var _each = angular.forEach,
            _self = UserServices.server.app({}),
            _handler = function(response, action) {
                _each(_response_actions, function(action, name) {
                    if(response.hasOwnProperty(name)) _response_actions[name](response);
                })
            },
            _response_actions = {
            };

//        ResponseMessage.handlers.push( _handler );

        return _self;
    }]);

userServices.factory('User', ['UserServices', 'ResponseMessage',
    function(UserServices, ResponseMessage) {

        var _each = angular.forEach,
            _self = UserServices.server.info({}, function(response) {
                ResponseMessage.handleMessages(response, 'User');
            }),
            _handler = function(response, action) {
                _each(_response_actions, function(action, name) {
                    if(response.hasOwnProperty(name)) _response_actions[name](response);
                })
            },
            _response_actions = {
                'add_teams' : function(response) {
                    _each( response.add_teams, function(name, key) {
                        _self.teams[key] = name;
                    });
                },
                'add_team_leads' : function(response) {
                    _each( response.add_team_leads, function(name, key) {
                        _self.team_leads[key] = name;
                    });
                },
                'remove_team' : function(response) {
                    if(_self.teams.hasOwnProperty(response.remove_team)) {
                        delete _self.teams[response.remove_team];
                    }
                },
                'remove_team_lead' : function(response) {
                    if(_self.team_leads.hasOwnProperty(response.remove_team_lead)) {
                        delete _self.team_leads[response.remove_team_lead];
                    }
                }
            };

        ResponseMessage.handlers.push( _handler );

        //console.log('User', _self);

        return _self;
    }]);

userServices.factory('UserCampaigns', ['UserServices', 'ResponseMessage', '$rootScope', '$location',
    function(UserServices, ResponseMessage, $rootScope, $location) {

        var _each = angular.forEach,
            _self = UserServices.server.campaigns({}, function() {

                _self.$hasActive = false;
                _self.$hasComplete = false;

                _each(_self, function(campaign) {
                    _activeCheck(campaign);
                    _completeCheck(campaign);
                });

                if(!_self.$hasActive) $rootScope.campaignView = '';
            }),
            _handler = function(response, action) {
                _each(_response_actions, function(action, name) {
                    if(response.hasOwnProperty(name)) _response_actions[name](response);
                })
            },
            _response_actions = {
                'add_campaign' : function(response) {
                    _self.unshift(response.add_campaign);
                    _activeCheck(response.add_campaign);
                    $rootScope.campaignView = 'active';
                },
                'complete_campaign' : function(response) {
                    _each(_self, function(campaign) {
                        if(campaign.id == response.complete_campaign) {
                            campaign.status = 'complete';
                            _completeCheck(campaign);
                            switch($location.$$path) {
                                case '/my-account/teams/' :
                                    _each($rootScope.team.toManage.campaigns, function(campaign) {
                                        if(campaign.id == response.complete_campaign) {
                                            campaign.status = 'complete';
                                            $rootScope.team.campaignView = 'complete';
                                        }
                                    });
                                    break;
                                default :
                                    $rootScope.campaignView = 'complete';

                            }
                        }
                    });
                }
            },
            _activeCheck = function(campaign) {
                _self.$hasActive||(_self.$hasActive = campaign.status == 'active');
            },
            _completeCheck = function(campaign) {
                _self.$hasComplete||(_self.$hasComplete = campaign.status == 'complete');
            };

        _self.$hasActive = false;
        _self.$hasComplete = false;

        ResponseMessage.handlers.push( _handler );

        return _self;
    }]);

userServices.factory('Team', ['UserServices', 'ResponseMessage',
    function(UserServices, ResponseMessage) {

        var _each = angular.forEach,
            _self = {},
            _handler = function(response, action) {
                _each(_response_actions, function(action, name) {
                    if(response.hasOwnProperty(name)) _response_actions[name](response);
                });


            },
            _response_actions = {
                'team_to_manage' : function(response) {
                    _self.campaignView = 'active';
                    _self.toManage = response.team_to_manage;
                    console.log(_self.toManage);
                },
                'promote_user' : function(response) {
                    _self.toManage.members[response.promote_user].is_leader = true;
                }
            };

        ResponseMessage.handlers.push( _handler );

        return _self;
    }]);

//userServices.factory('Campaign', ['UserServices', 'ResponseMessage',
//    function(UserServices, ResponseMessage) {
//
//        var _each = angular.forEach,
//            _self = {},
//            _handler = function(response, action) {
//                _each(_response_actions, function(action, name) {
//                    if(response.hasOwnProperty(name)) _response_actions[name](response);
//                });
//
//
//            },
//            _response_actions = {
//                'team_to_manage' : function(response) {
//                    _self.toManage = response.team_to_manage;
//                }
//            };
//
//        ResponseMessage.handlers.push( _handler );
//
//        return _self;
//    }]);

document.createElement('message');
userServices.directive('message', ['$location', '$compile', 'ResponseMessage',
    function($location, $compile, ResponseMessage) {
        return {
            restrict: 'AE',
            link: function($scope, element, attrs) {

                var _messageScope = $scope.$new(),
                    _each = angular.forEach, _element = angular.element,
                    _prepend = attrs.hasOwnProperty('prepend');

                //  Plain Text Messages
                var _templates = {};

                _templates.errors = {
                    class: 'alert-danger'
                }
                _templates.messages = {
                    class: 'alert-info'
                }

                _messageScope.messages = ResponseMessage.getMessages(attrs.path);
                _messageScope.messageGroup = attrs.messageGroup;

                //  Create an alert element for each message type
                _each(_templates, function(props, type) {
                    var _messageBlock = _element('<div class="alert"></div>'),
                        _messages = _element('<div></div>');

                    _messages.attr('ng-repeat', 'message in messages[\''+type+'\'][messageGroup]');
                    _messages.attr('ng-bind', 'message');

                    _messageBlock.attr('ng-show', 'messages[\''+type+'\'][messageGroup]');
                    _messageBlock.addClass( props.class );
                    _messageBlock.html(_messages);

                    _prepend ? element.prepend(_messageBlock) : element.append(_messageBlock);

                    $compile(_messageBlock)(_messageScope);
                });


                //  Action Prompts
                var _promptBlock = _element('<div></div>'),
                    _prompt = _element('<div><span ng-bind="prompt.message"></span></div>'),
                    _promptDismiss, _promptAccept;


                _prompt.attr('class', 'alert alert-prompt alert-{{prompt.level}} clearfix');
                _prompt.attr('ng-repeat', 'prompt in prompts');


                _promptAccept = _element( '<button ng-show="prompt.hasOwnProperty(\'accept\')">Accept</button>' );
                _promptAccept.attr( 'class', 'btn btn-xs btn-primary pull-right' );
                _promptAccept.attr( 'user-action', 'user-action' );
                _promptAccept.attr( 'action', 'acceptPrompt' );
                _promptAccept.attr( 'no-classes', 'no-classes' );
                _promptAccept.attr( 'send-prompt', 'prompt' );
                _promptAccept.attr( 'send-prompt-group', 'messageGroup' );
                _prompt.append(_promptAccept);


                _promptDismiss = _element( '<button>Dismiss</button>' );
                _promptDismiss.attr( 'class', 'btn btn-xs btn-danger pull-right space-horz-xs' );
                _promptDismiss.attr( 'user-action', 'user-action' );
                _promptDismiss.attr( 'action', 'dismissPrompt' );
                _promptDismiss.attr( 'no-classes', 'no-classes' );
                _promptDismiss.attr( 'send-prompt', 'prompt' );
                _promptDismiss.attr( 'send-prompt-group', 'messageGroup' );
                _prompt.append(_promptDismiss);

                _promptBlock.attr('ng-repeat', 'prompts in messages[\'prompts\'][messageGroup]');
                _promptBlock.html(_prompt);

                _prepend ? element.prepend(_promptBlock) : element.append(_promptBlock);

                $compile(_promptBlock)(_messageScope);

                ResponseMessage.handlers.push(function(response) {
                    if(response.hasOwnProperty('dismiss_prompt')) {
                        // Now there's a selector!

                        _each(_messageScope.messages['prompts'][_messageScope.messageGroup], function(prompts, key) {
                            _each(prompts, function(prompt, id) {
                                if(id == response['dismiss_prompt']) {
                                    delete _messageScope.messages['prompts'][_messageScope.messageGroup][key][id];
                                }
                            });
                        });

                        ResponseMessage.hasMessages(attrs.path);
                    }
                });


            }
        };
    }]);

userServices.directive('userAction', ['$compile', '$timeout', 'UserServices', 'User',
    function($compile, $timeout, UserServices, User) {
        return {
            restrict: 'A',
            link: function($scope, element, attrs) {

                var _each = angular.forEach, _element = angular.element,
                    _actionScope = $scope.$new(),
                    _isSelect = element[0].tagName == 'SELECT',
                    _doAction, _addClasses, _removeClasses,
                    _trigger, _dataModels = {}, _states = {}, _text, _icon, _stateClasses = !attrs.hasOwnProperty('noClasses');

                _trigger = _isSelect ? 'change' : 'click';

                if(!_isSelect) {
                    if(_stateClasses) element.addClass('btn');

                    _removeClasses = function(state) {
                        _each(_states[state].classes, function(className) {
                            element.removeClass(className);
                        });
                    }


                    _addClasses = function(state) {
                        _each(_states[state].classes, function(className) {
                            element.addClass(className);
                        });
                    }


                    //  Button States
                    _states.ready = {
                        label: element.text(),
                        icon: '',
                        classes: ['btn-primary']
                    }
                    _states.pending = {
                        label: 'Saving',
                        icon: 'spinner',
                        classes: ['btn-primary']
                    }
                    _states.success = {
                        label: 'Success',
                        icon: 'check',
                        classes: ['btn-success']
                    }
                    _states.error = {
                        label: 'Error',
                        icon: 'minus-circle',
                        classes: ['btn-danger']
                    }

                    _actionScope.label = '';
                    _actionScope.icon = '';

                    _actionScope.state = '';
                    _actionScope.$watch('state', function(newValue, oldValue) {
                        if(_stateClasses) {
                            if(_states.hasOwnProperty(oldValue)) _removeClasses(oldValue);
                            if(_states.hasOwnProperty(newValue)) _addClasses(newValue);
                        }
                        _actionScope.label = _states[newValue].label;
                        _actionScope.icon = _states[newValue].icon;
                    });

                    //  Text Label Setup
                    _text = _element('<span ng-bind="label"></span>');
                    element.html(_text);
                    $compile(_text)(_actionScope);

                    //  Icon Setup
                    _icon = _element('<i class="fa fa-{{icon}}"></i>');
                    _icon.attr('ng-class', "{ 'fa-spin' : state == 'pending' }");

                    if(attrs.hasOwnProperty('appendIcon')) {
                        element.append('&nbsp;');
                        element.append(_icon);
                    } else {
                        element.prepend('&nbsp;');
                        element.prepend(_icon);
                    }

                    $compile(_icon)(_actionScope);



                    _each(_states, function(props, state) {
                        // Override default text labels by attributes if present
                        var textAttr = state+'Text';
                        if(attrs.hasOwnProperty(textAttr)) {
                            _states[state].label = attrs[textAttr];
                        }
                        // Override default icons by attributes if present
                        var iconAttr = state+'Icon';
                        if(attrs.hasOwnProperty(iconAttr)) {
                            _states[state].icon = attrs[iconAttr];
                        }
                    });
                }


                //  Data Assignments
                _each(attrs, function(model, attr) {
                    if(/^send/.test(attr)) {
                        var name = attr.slice(4),
                            parts = name.match(/[A-Z]?[a-z]+/g),
                            serverName = '';

                        _each(parts, function(part) {
                            part = part.toLowerCase();
                            serverName += serverName ? '_'+part : part;
                        });

                        _dataModels[serverName] = model;
                    }
                });


                _doAction = function(e) {
                    if(_actionScope.state == 'ready') {
                        if(attrs.hasOwnProperty('confirm')) {
                            if( !confirm( attrs.confirm ) ) return;
                        }

                        _actionScope.state = 'pending';

                        var _data = {}, _cb;

                        _cb = function(response) {

//                            console.log(response);

                            //  Response state
                            _actionScope.state = response.hasOwnProperty('errors') ? 'error' : 'success';

                            //  Ready state reset
                            if(element[0].tagName == 'SELECT') {
                                _actionScope.state = 'ready';
                            } else {
                                $timeout(function() {
                                    _actionScope.state = 'ready';
                                }, 3000);
                            }

                            if(attrs.hasOwnProperty('callback')) {
                                $scope.$eval(attrs.callback)(response);
                            }
                        }

                        _each(_dataModels, function(model, key) {
                            _data[key] = $scope.$eval(model);
                        });

                        if(_isSelect && attrs.hasOwnProperty('selectName')) {
                            _data[attrs.selectName] = element.val();
                        }

                        UserServices.server[attrs.action](_data, _cb);
                    }
                }

                element.on(_trigger, _doAction);

                _actionScope.state = 'ready';

            }
        };
    }]);