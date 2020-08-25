/* eslint-disable no-unreachable */
/* eslint-disable no-undef */
/* eslint-disable one-var */
/* eslint-disable no-unused-vars */
import React, { Component } from 'react';
import { inject, observer, Provider } from 'mobx-react';
import AppComponent from './app.component';
@inject('appStore')
@observer
class App extends Component {
    render() {
        const { children, appStore, className } = this.props;

        return (
            <div className={className}>
                <Provider appStore={appStore}>
                    <div>
                        <AppComponent />
                    </div>
                </Provider>
                {children}
            </div>
        );
    }
}

export default App;
