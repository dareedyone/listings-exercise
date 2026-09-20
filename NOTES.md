# Notes

## Key decisions

- Saved searches reuse the existing listing search criteria for property type, region, minimum bedrooms and maximum price.
- A `ListingWentLive` event is used as the integration point for matching. The repository does not currently have a listing publishing workflow, so the event is dispatched explicitly in the demo seed data and tests.
- Matches are persisted as `SavedSearchMatch` records. This keeps the fact that a listing matched a particular saved search separate from how alerts are presented or eventually delivered.
- Alerts are grouped by listing, so if a listing matches multiple saved searches, the user sees one alert showing the searches it matched. This also leaves room for grouping or digesting notifications later.
- Only listings becoming live after a saved search exists are considered. Existing live listings are not backfilled.

## Deliberately left out

- Email, push notifications and queue workers were not implemented. The persisted matches provide the foundation for those delivery mechanisms.
- Additional saved-search criteria in the persistence model, such as bathroom ranges and postcode, are not exposed or matched because they are not part of the existing listing search UI.
- Saved-search editing was not included because the exercise only requires creation, viewing and deletion.

## At scale

The current matching listener loads all matching saved searches and creates matches individually. For a larger dataset, I would queue the work, process searches in chunks or use a more targeted/bulk insert strategy, and review the relevant database indexes.

The alerts page currently loads the user's matches and groups them in application memory. At higher volumes, I would paginate the underlying data and consider database-level grouping or a separate notification/digest model depending on the product requirements.

With more time, I would also connect `ListingWentLive` to the actual listing publishing workflow and add asynchronous notification delivery.