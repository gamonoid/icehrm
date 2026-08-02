/**
 * LeaveCalcParser — turns the free-text "How is this calculated?" leave
 * entitlement log (an array of strings produced by LeavesActionManager's
 * LogManager::collectLogs) into a structured model that a chart can render.
 *
 * The log has two parts:
 *  1. A header describing the current period entitlement:
 *       Leave Type (Annual leave) [id (1)]
 *       Leave Period After Adjust : 2026-01-01 - 2026-12-31
 *       Total for leave for current period: 14
 *       Total after adjusted based on joined date: 14
 *       Total after leave accrue: 7.154
 *       Number of past leave periods: 0
 *  2. Zero or more carry-forward blocks, one per past period, each delimited by
 *     "<hr/>" and starting with "Calculating leave days carried forward from
 *     [Period FROM - TO]", then carried-in / allocated / taken / carried-to-next.
 *
 * Lines may be prefixed with "(client=<name>) " and the first leave type in a
 * batch is preceded by "Calculating Leave Entitlement" + the employee name;
 * both are ignored here.
 */

const stripPrefix = (line) => String(line == null ? '' : line)
  .replace(/^\(client=[^)]*\)\s*/, '')
  .trim();

const toNum = (s) => {
  const n = Number(s);
  return Number.isFinite(n) ? n : 0;
};

export default class LeaveCalcParser {
  constructor(lines) {
    this.lines = Array.isArray(lines) ? lines.map(stripPrefix) : [];
  }

  /**
   * @returns {{
   *   leaveType: string, leaveTypeId: (number|null),
   *   period: ({from: string, to: string}|null),
   *   totalForPeriod: (number|null), afterJoinedDate: (number|null),
   *   afterAccrue: (number|null), accrualApplied: boolean,
   *   joinedDateAdjusted: boolean, pastPeriodCount: number,
   *   carryForward: Array<{
   *     label: string, from: string, to: string,
   *     carriedIn: number, allocated: number, pto: number,
   *     taken: number, deducted: number, validTill: string, carriedToNext: number,
   *   }>,
   * }}
   */
  parse() {
    const model = {
      leaveType: '',
      leaveTypeId: null,
      period: null,
      totalForPeriod: null,
      afterJoinedDate: null,
      afterAccrue: null,
      accrualApplied: false,
      joinedDateAdjusted: false,
      pastPeriodCount: 0,
      carryForward: [],
    };

    let current = null;
    const flush = () => {
      if (current) { model.carryForward.push(current); current = null; }
    };

    this.lines.forEach((line) => {
      if (!line || line === '<hr/>') return;
      let m;

      if ((m = line.match(/^Leave Type \((.+)\) \[id \((\d+)\)\]$/))) {
        if (!model.leaveType) { model.leaveType = m[1]; model.leaveTypeId = Number(m[2]); }
        return;
      }
      if ((m = line.match(/^Leave Period After Adjust\s*:\s*(\d{4}-\d{2}-\d{2})\s*-\s*(\d{4}-\d{2}-\d{2})/))) {
        model.period = { from: m[1], to: m[2] };
        return;
      }
      if ((m = line.match(/^Total for leave for current period:\s*([\d.]+)/))) {
        model.totalForPeriod = toNum(m[1]); return;
      }
      if ((m = line.match(/^Total after adjusted based on joined date:\s*([\d.]+)/))) {
        model.afterJoinedDate = toNum(m[1]); return;
      }
      if ((m = line.match(/^Total after leave accrue:\s*([\d.]+)/))) {
        model.afterAccrue = toNum(m[1]); return;
      }
      if ((m = line.match(/^Number of past leave periods:\s*(\d+)/))) {
        model.pastPeriodCount = Number(m[1]); return;
      }

      if ((m = line.match(/^Calculating leave days carried forward from \[Period\s*(\d{4}-\d{2}-\d{2})\s*-\s*(\d{4}-\d{2}-\d{2})\]/))) {
        flush();
        current = {
          from: m[1],
          to: m[2],
          label: m[1].slice(0, 4),
          carriedIn: 0,
          allocated: 0,
          pto: 0,
          taken: 0,
          deducted: 0,
          validTill: '',
          carriedToNext: 0,
        };
        return;
      }

      if (!current) return; // remaining patterns only apply inside a carry block

      if ((m = line.match(/^Number of leaves carried from previous period:\s*([\d.-]+)/))) {
        current.carriedIn = toNum(m[1]); return;
      }
      if ((m = line.match(/^Number of allocated \[.*\]:\s*leave\(([\d.-]+)\)\s*\+\s*PTO\(([\d.-]+)\)\s*=\s*([\d.-]+)/))) {
        current.allocated = toNum(m[1]); current.pto = toNum(m[2]); return;
      }
      if ((m = line.match(/^Leave days from previous period is valid till:\s*(\d{4}-\d{2}-\d{2})/))) {
        current.validTill = m[1]; return;
      }
      if ((m = line.match(/^Total number of leave days taken between .*:\s*([\d.-]+)/))) {
        current.taken = toNum(m[1]); return;
      }
      if ((m = line.match(/^Number of leave deducted from carried forward leaves from previous period:\s*([\d.-]+)/))) {
        current.deducted = toNum(m[1]); return;
      }
      if ((m = line.match(/^Number of leaves carried to next period:\s*([\d.-]+)/))) {
        current.carriedToNext = toNum(m[1]); return;
      }
    });
    flush();

    const near = (a, b) => a != null && b != null && Math.abs(a - b) > 1e-9;
    model.accrualApplied = near(model.afterAccrue, model.afterJoinedDate);
    model.joinedDateAdjusted = near(model.afterJoinedDate, model.totalForPeriod);

    return model;
  }

  /** Convenience: parse and return only the carry-forward periods. */
  static parse(lines) {
    return new LeaveCalcParser(lines).parse();
  }
}
