# Order Status Transition Audit

## Registry

The current database statuses are `pending`, `processing`, `shipping`, `completed` and `cancelled`. PR3C will make this list server-owned through `OrderStatusRegistry`.

## Allowed transitions

| From | Allowed to |
| --- | --- |
| pending | processing, cancelled |
| processing | shipping, cancelled |
| shipping | completed |
| completed | none |
| cancelled | none |

Submitting the current status is a no-op: it must not create history, change the version or mutate stock.

## Current gaps

The admin controller validates membership only and writes the status directly. It does not enforce the transition table, require a version, require a cancellation reason, lock the row or record an actor/history entry.

## Target invariant

Every non-no-op mutation is performed in one transaction after locking the order, comparing the submitted version, validating the registry transition and creating one history row. Terminal statuses cannot be changed.
