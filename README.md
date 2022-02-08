# Logs monitoring

Provides endpoint that indicate about errors in the log files.

## Configuration
In the form /admin/config/development/logs-monitoring-settings should be set:
1) paths to logs
2) a count of rows in the end where a search will be performed
3) a list of the words that indicate errors.

### Usage
The module provide REST endpoint /admin/reports/logs-monitoring where a list
of logs with their statuses will be provided. It can be used by Uptimerobot to detect errors.
Returns HTTP status codes:
- 200 - no errors found
- 500 - some errors found
