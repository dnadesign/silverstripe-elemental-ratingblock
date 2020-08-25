/*
 * App + this file is an example setup and not required by the final application.
 * Login, and the shared folder are the core of this library
 * */

import React from 'react';
import ReactDOM from 'react-dom';
import { Provider } from 'mobx-react';
import AppStore from './rating/app.store';
import App from './rating/app';
import { HTTPStore } from './http/http.store';

// Page name allows us to store a reference to the page without relying on a page relation
const pageName = (window.bootData && window.bootData.RatingPageName) ? window.bootData.RatingPageName : 'unknown',
    httpStore = HTTPStore.getInstance(),
    appStore = AppStore.getInstance({
        httpStore: httpStore,
        pageName: pageName
    }),
    rootStores = {
        httpStore,
        appStore
    };

ReactDOM.render(
    <Provider {...rootStores}>
        <App className='rating-app' />
    </Provider>,
    document.querySelector('[data-ratingblock]')
);

if (module.hot) {
    module.hot.accept();
}
