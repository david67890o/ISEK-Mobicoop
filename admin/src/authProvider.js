import { AUTH_LOGIN, AUTH_LOGOUT, AUTH_ERROR, AUTH_CHECK } from 'react-admin';
import decodeJwt from 'jwt-decode';
import { AUTH_GET_PERMISSIONS } from 'ra-core';

require('dotenv').config();

// Api URI
const uri = process.env.REACT_APP_API;
// Authentication token URI
const authenticationTokenUri = process.env.REACT_APP_API_LOGIN;

// function to search for a given permission
function isAuthorized(action) {
    let permissions = JSON.parse(localStorage.getItem('permissions'));
    return permissions.hasOwnProperty(action);
}

export default (type, params) => {
    switch (type) {
        case AUTH_LOGIN:
            const { username, password } = params;
            // first request to check authentication
            const request = new Request(authenticationTokenUri, {
                method: 'POST',
                body: JSON.stringify({ username: username, password: password }),
                headers: new Headers({ 'Content-Type': 'application/json' }),
            });

            return fetch(request)
                .then(response => {
                    if (response.status < 200 || response.status >= 300) throw new Error(response.statusText);
                    return response.json();
                })
                .then(({ token }) => {
                    const decodedToken = decodeJwt(token);
                    var authorized = decodedToken.roles.find(function(element) {
                        return (element === 'ROLE_ADMIN' || element === 'ROLE_SUPER_ADMIN');
                    });
                    if (!authorized) throw new Error('Unauthorized');
                    localStorage.setItem('token', token);
                    localStorage.setItem('roles', decodedToken.roles);
                    localStorage.setItem('id', decodedToken.id);
                    return {id: decodedToken.id, token};
                })
                .then(({id,token}) => {
                    // second request to get all the permissions
                    const requestPermissions = new Request(`${uri}users/${id}/permissions`, {
                        method: 'GET',
                        headers: new Headers({ 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` }),
                    });
                    return fetch(requestPermissions)
                        .then(response => {
                            if (response.status < 200 || response.status >= 300) throw new Error(response.statusText);
                            return response.json();
                        })
                        .then(({permissions}) => {
                            localStorage.setItem('permissions', JSON.stringify(permissions));
                        });
                });

        case AUTH_LOGOUT:
            localStorage.removeItem('token');
            localStorage.removeItem('roles');
            localStorage.removeItem('id');
            localStorage.removeItem('permissions');
            return Promise.resolve();

        case AUTH_ERROR:
            if (401 === params.response.status || 403 === params.response.status) {
                localStorage.removeItem('token');
                localStorage.removeItem('roles');
                localStorage.removeItem('id');
                localStorage.removeItem('permissions');
                return Promise.reject();
            }
            return Promise.resolve();

        case AUTH_CHECK:
            return localStorage.getItem('token') ? Promise.resolve() : Promise.reject({ redirectTo: '/login' });

        case AUTH_GET_PERMISSIONS:
            let permission;
            if (params && params.location) {
                switch (params.location) {
                    // create a use case for each resource route
                    // can be divided if permission must be more granular (eg. permissions on field level)
                    case "/users":
                        permission = isAuthorized("user_manage");
                        break;
                    case "/communities":
                        permission = isAuthorized("community_manage");
                        break;
                    case "/roles":
                        permission = isAuthorized("permission_manage");
                        break;
                    case "/rights":
                        permission = isAuthorized("permission_manage");  
                        break;
                    case "/relay_points":
                        permission = isAuthorized("relay_point_manage");
                        break;
                    case "/relay_point_types": 
                        permission = isAuthorized("relay_point_manage");
                        break;
                    case "/community_users":
                        permission = isAuthorized("community_manage");
                        break;
                    case "/articles":
                        permission = isAuthorized("article_manage");
                        break;
                    case "/sections": 
                        permission = isAuthorized("article_manage");
                        break;
                    case "/paragraphs": 
                        permission = isAuthorized("article_manage");
                        break;
                    case "/territories": 
                        permission = isAuthorized("territory_manage");
                        break;
                    default:break;
                }
            } else {
                return true;
            }
            return permission ? Promise.resolve(permission) : Promise.reject();
            
        default:
            return Promise.resolve();
    }
}