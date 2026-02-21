const computeTotal = (subjects) => Object.values(subjects || {}).reduce((sum, mark) => sum + Number(mark || 0), 0);

const resolveGrade = (percentage, gradingConfig = []) => {
  const sorted = [...gradingConfig].sort((a, b) => b.min - a.min);
  const matched = sorted.find((rule) => percentage >= Number(rule.min));
  return matched?.grade || 'F';
};

const buildComputedResult = ({ subjects, totalMarks, passMark, gradingConfig }) => {
  const total = computeTotal(subjects);
  const percentage = totalMarks > 0 ? Number(((total / totalMarks) * 100).toFixed(2)) : 0;
  const grade = resolveGrade(percentage, gradingConfig);
  const result_status = total >= passMark ? 'PASS' : 'FAIL';

  return { total, percentage, grade, result_status };
};

module.exports = { buildComputedResult, computeTotal, resolveGrade };
