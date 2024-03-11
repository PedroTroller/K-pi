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

## Integration with CI

### Github Actions

## Some examples

For all examples, please refer to the configuration file `.k-pi.dist.yaml`.

### Track Github dependabot security alerts
