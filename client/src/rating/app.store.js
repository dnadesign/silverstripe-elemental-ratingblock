/* eslint-disable no-unused-vars */
import { observable, action } from 'mobx';
import gql from 'graphql-tag';
import Cookie from 'mobx-cookie';
import { HttpLink } from 'apollo-link-http';
import ApolloClient from 'apollo-client';
import { InMemoryCache } from 'apollo-cache-inmemory';

const RatingMutation = gql`
    mutation RatingMutation(
        $Rating: Int!
        $Comments: String
        $Tags: String
        $PageName: String!
        $URL: String!
        $PageID: Int
    ) {
        ratingMutation(
            Rating: $Rating
            Comments: $Comments
            Tags: $Tags
            PageName: $PageName
            URL: $URL
            PageID: $PageID
        ) {
            ID
            Rating
            Comments
            Tags
            PageName
            PageID
            URL
            Error
        }
    }
`;

export default class RatingStore {
    httpStore;

    // reference for page name
    pageName = 'unknown';

    static getInstance(props) {
        return (
            RatingStore.instance || (RatingStore.instance = new RatingStore(props))
        );
    }

    constructor(props) {
        this.httpStore = props.httpStore;
        this.pageName = props.pageName;

        this.httpLink = new HttpLink({
            uri: this.getApiUri(),
            credentials: 'same-origin'
        });

        this.apolloClient = this.setApolloClient();
    }

    getApiUri() {
        const location = window.location.host;

        if (
            window.location.hostname === 'localhost' ||
            window.location.hostname === '10.0.2.2'
        ) {
            return '//mbie9.test/ratingblockgraphql/';
        }

        return `//${location}/ratingblockgraphql/`;
    }

    setApolloClient() {
        return new ApolloClient({
            link: this.httpStore.authLink.concat(this.httpLink),
            cache: new InMemoryCache().restore(window.__APOLLO_STATE__)
        });
    }

    @observable error = null;

    @observable loading = false;

    @observable submitted = false;

    // Store value in a mobx-observable cookie
    @observable cookie = new Cookie(this.getCookieName('RateElement'));

    setCookiePage() {
        const cookieValue = this.cookie.value;
        if (cookieValue && cookieValue !== 'undefined') {
            const values = JSON.parse(cookieValue),
                valueIndex = values.findIndex(item => item.url === window.location.href);

            if (valueIndex === -1) {
                values.push({ url: window.location.href });
                this.cookie.set(JSON.stringify(values), { expires: 1 });
            }
        } else {
            const values = [{ url: window.location.href }];
            this.cookie.set(JSON.stringify(values), { expires: 1 });
        }
    }

    getCookieName(cookieType) {
        const encoded = window.btoa(cookieType);
        return encoded;
    }

    getRateValue() {
        const cookieValue = this.cookie.value;
        if (cookieValue) {
            const values = JSON.parse(cookieValue),
                valueIndex = values.findIndex(item => item.url === window.location.href);

            if (valueIndex > -1) {
                return values[valueIndex].rating;
            }
        }

        return null;
    }

    getTagsValue() {
        const cookieValue = this.cookie.value;
        if (cookieValue) {
            const values = JSON.parse(cookieValue),
                valueIndex = values.findIndex(item => item.url === window.location.href);

            if (valueIndex > -1) {
                return values[valueIndex].tags;
            }
        }

        return null;
    }

    setRateValue(value) {
        const cookieValue = this.cookie.value,
            values = cookieValue ? JSON.parse(this.cookie.value) : {},
            valueIndex = values.findIndex(item => item.url === window.location.href);

        if (valueIndex > -1) {
            values[valueIndex]['rating'] = value;
            this.cookie.set(JSON.stringify(values), { expires: 1 });
        }
    };

    setTagsValue(value) {
        const values = JSON.parse(this.cookie.value),
            valueIndex = values.findIndex(item => item.url === window.location.href);

        if (valueIndex > -1) {
            values[valueIndex]['tags'] = value;
        }
    };

    // Push rating to backend via graphql
    async rate(values) {
        this.loading = true;
        this.result = {};

        const response = await this.apolloClient
            .mutate({
                mutation: RatingMutation,
                variables: {
                    Rating: values.rating,
                    Comments: values.comments,
                    Tags: values.tags,
                    PageName: values.pageName,
                    URL: window.location.toString(),
                    PageID: values.pageID
                },
                fetchPolicy: 'no-cache'
            })
            .catch(response => {
                const errors = response.graphQLErrors.map(error => error.message);
                this.error = errors.join(', ');
            });

        if (response && response.data && response.data.ratingMutation) {
            this.setCookiePage();
            this.setRateValue(values.rating);
            this.setTagsValue(values.tags);
            this.submitted = true;
            this.loading = false;
        }
    }
}
