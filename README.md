# DataCite DOI List

List of DataCite DOIs extracted from [DataCite Public Data File 2024](https://doi.org/10.14454/tjpc-9m93). Need to send email requesting access link (which expires after 24 hours). Download is 24 Gb tar file, which then expands to folders of the form `updated_year-month`. Each folder contains a gzipped CSV file with a list of DOIs updated that month, and one or more JSONL files with metadata for those DOIs.

## Uses

DataCite DOI search using the API is limited to 10,000 records, and the bibliographic metadata in DataCite is often a bit ropey. This makes understanding what is in DataCite difficult. For example, finding all DOIs for a journal, or discovering how many DNA barcoding BINs have DOIs. Hence this code to parse and extract a list of DOIs. The list is stored in a SQLite database.

We then need to add code to find the DOIs in the compressed JSONL files if we want the underlying metadata.

### Get DOIs for BOLD BINs

```
SELECT doi FROM doi WHERE doi LIKE "10.5883/bold%";
```

144,453 DOIs for BINs.

### Get DOIs for BOLD datasets

```
SELECT doi FROM doi WHERE doi LIKE "10.5883/ds%";
```

2,340 DOIs for datasets.

## Schema

```
CREATE TABLE "doi" (
    doi TEXT PRIMARY KEY
  , state TEXT
  , client_id TEXT
  , updated TEXT
  , path TEXT
  , "row" INTEGER
);

CREATE INDEX client_id_idx ON doi(client_id COLLATE BINARY ASC);

CREATE INDEX updated_idx ON doi(updated COLLATE BINARY ASC);

CREATE INDEX doi_idx ON doi(doi COLLATE NOCASE ASC);
```





