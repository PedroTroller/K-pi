The goal of this project is to make it easy to track metrics.

## Configuration

Just create a `.k-pi.dist.yaml` file following the following pattern:

```yaml
---
reports:
  <report-name>:
    storage:
      <storage-type>: <storage-config>
```

### Reference

#### `reports.<report-name>.extra`

If a report is compiled in one of the configured `storages`, then compiled
values can be added to it.

#### `reports.<report-name>.extra.total`

Add total metrics values to compiled report. Expected value is the label of the
metric.

```yaml
---
reports:
  <report-name>:
    extras:
      total: The total
    # ...
```

#### `reports.<report-name>.colors`

Customize metrics colors on compiled reports. The expected value is a key-value
with the metric label as key, and a hex color code as value.

```yaml
---
reports:
  <report-name>:
    colors:
      The total: "#CDCDCD"
      Metric 1: "#F2D6B1"
    # ...
```

#### `reports.<report-name>.precision`

Allows you to specify the precision to which metrics will be rounded. The
default setting is `2`.

#### `reports.<report-name>.storage`

Allows you to configure the way in which the metrics recorded in the report are
stored.

#### `reports.<report-name>.storage.github-discussion`

Integration with Github discussions.

#### `reports.<report-name>.storage.github-discussion.url`

Url of the target discussion (e.g.
https://github.com/KnpLabs/K-pi/discussions/1).

#### `reports.<report-name>.check-reporter`

Configure how metrics evolution is notified.

#### `reports.<report-name>.check-reporter.github-status`

Integration with Github status on pull-requests.

#### `reports.<report-name>.check-reporter.github-status.states`

#### `reports.<report-name>.check-reporter.github-status.states.on-lower`

#### `reports.<report-name>.check-reporter.github-status.states.on-higher`

#### `reports.<report-name>.check-reporter.github-status.unit`

#### `reports.<report-name>.check-reporter.github-status.unit.plural`

#### `reports.<report-name>.check-reporter.github-status.unit.singular`

## Integration with CI

### Github Actions

## Some examples

For all examples, please refer to the configuration file
[`.k-pi.dist.yaml`](./.k-pi.dist.yaml).

### Track test coverage

> See [`example-coverage.yaml`](./.github/workflows/example-coverage.yaml)

### Track Github dependabot security alerts

> See
> [`example-github-security.yaml`](./.github/workflows/example-github-security.yaml)

The goal of this metric is to count the number of alerts from dependabot's
Github API and project them onto a graph to track their evolution over time.

In the configuration file, you can see that the configured storage is
`github-discussion`. It means that data will be stored in the discussion but
also projected onto a graph.

### Track PhpMetrics data

> See [`example-phpmetrics.yaml`](./.github/workflows/example-phpmetrics.yaml)
