import { observable, action, computed, reaction } from 'mobx';
import ApolloClient from 'apollo-client';
import { HttpLink } from 'apollo-link-http';
import { InMemoryCache } from 'apollo-cache-inmemory';
import { ApolloLink } from 'apollo-link';

export class HTTPStore {
    static instance: HTTPStore;

    @observable error = false;
    @observable rootURL = false;
    @observable loading = false;
    @observable token = false;
    @observable apolloClient = null; // set from reaction

    baseURL;

    constructor() {
        this.setApolloClient();

        reaction(
            () => this.token,
            token => {
                this.setApolloClient();
            }
        );
    }

    @action clearError() {
        this.error = false;
    }

    @action setToken(token) {
        this.token = token;
    }

    static getInstance() {
        return HTTPStore.instance || (HTTPStore.instance = new HTTPStore());
    }

    /**
     * Get a generic error message
     */
    static getError() {
        return 'An error occurred. Please try again later.';
    }

    /**
     * get the graphql endpoint used by the app
     */
    getApiUri() {
        const location = window.location.host;

        if (
            window.location.hostname === 'localhost'
        ) {
            return '//localhost/graphql/';
        }

        return `//${location}/graphql/`;
    }

    httpLink = new HttpLink({
        uri: this.getApiUri(),
        credentials: 'same-origin'
    });

    authLink = new ApolloLink((operation, forward) => {
        // Use the setContext method to set the HTTP headers.
        operation.setContext({
            headers: {
                authorization: this.headers
            }
        });

        // Call the next link in the middleware chain.
        return forward(operation);
    });

    @computed get headers() {
        return this.token ? `Bearer ${this.token}` : '';
    }

    setApolloClient() {
        this.apolloClient = new ApolloClient({
            link: this.authLink.concat(this.httpLink),
            cache: new InMemoryCache().restore(window.__APOLLO_STATE__)
        });
    }

    /**
     * Catch Graphgql servererror
     * this will catch any eg 500 server errors
     * but not any errors returned in the payload data
     * (for a 200 response)
     *
     * @param result
     * @param store
     */
    handleServerError(result, store) {
        if (result.graphQLErrors) {
            const errors = result.graphQLErrors.map(error => error.message);
            store.error = {
                message: errors.join(', '),
                code: 500
            };
        } else if (result.networkError) {
            store.error = {
                message: result.networkError,
                code: 500
            };
        } else {
            store.error = {
                message: result,
                code: 500
            };
        }

        store.loading = false;
    }
}
