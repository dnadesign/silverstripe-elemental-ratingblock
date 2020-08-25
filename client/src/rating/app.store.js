/* eslint-disable no-unused-vars */
import { observable, action } from 'mobx';
import gql from 'graphql-tag';
import Cookie from 'mobx-cookie';

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

export default class AppStore {
    httpStore;

    // reference for page name
    pageName = 'unknown';

    static getInstance(props) {
        return (AppStore.instance || (AppStore.instance = new AppStore(props)));
    }

    constructor(props) {
        this.httpStore = props.httpStore;
        this.pageName = props.pageName;
    }

    @observable error = null;

    @observable loading = false;

    @observable submitted = false;

    // Store value in a mobx-observable cookie
    @observable rateCookie = new Cookie(this.getCookieName('Rate'));
    @observable tagsCookie = new Cookie(this.getCookieName('Tags'));

    getCookieName(cookieType) {
        const encoded = window.btoa(window.location.href + window.location.search);
        return `${this.pageName}${cookieType}-${encoded}`;
    }

    // get cookie
    setRateCookie(value) {
        this.rateCookie.set(value, { expires: 1 });
    };

    setTagsCookie(value) {
        this.tagsCookie.set(value, { expires: 1 });
    };

    // Push rating to backend via graphql
    async rate(values) {
        this.loading = true;
        this.result = {};

        const response = await this.httpStore.apolloClient
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
            this.setRateCookie(values.rating);
            this.setTagsCookie(values.tags);
            this.submitted = true;
            this.loading = false;
        }
    }
}
