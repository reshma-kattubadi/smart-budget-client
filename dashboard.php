<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

$username = $_SESSION['username'];

// Get current month in "Month Year" format, e.g. "May 2025"
$current_month = date('F Y');

// Get budget for current month
$stmt = $conn->prepare("SELECT amount FROM budgets WHERE username = ? AND month = ?");
$stmt->bind_param("ss", $username, $current_month);
$stmt->execute();
$stmt->bind_result($budget_amount);
$stmt->fetch();
$stmt->close();

if (!$budget_amount) {
    $budget_amount = 0;
}

// Get total expenses for current month
$stmt = $conn->prepare("SELECT SUM(amount) FROM expenses WHERE username = ? AND MONTH(expense_date) = MONTH(CURDATE()) AND YEAR(expense_date) = YEAR(CURDATE())");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($total_expenses);
$stmt->fetch();
$stmt->close();

if (!$total_expenses) {
    $total_expenses = 0;
}

// Calculate remaining budget
$remaining_budget = $budget_amount - $total_expenses;

?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Smart Budget Planner</title>
</head>
<body>
    <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
    <a href="logout.php">Logout</a>

    <h3>Your Budget Summary for <?php echo $current_month; ?>:</h3>
    <p><strong>Total Budget:</strong> ₹<?php echo number_format($budget_amount, 2); ?></p>
    <p><strong>Total Expenses:</strong> ₹<?php echo number_format($total_expenses, 2); ?></p>
    <p><strong>Remaining Budget:</strong> ₹<?php echo number_format($remaining_budget, 2); ?></p>

    <h3>Set Monthly Budget</h3>
    <form method="post" action="save_budget.php">
        <label>Amount: ₹</label>
        <input type="number" step="0.01" name="amount" required>
        <input type="hidden" name="month" value="<?php echo $current_month; ?>">
        <button type="submit">Save Budget</button>
    </form>

    <h3>Add Expense</h3>
    <form method="post" action="save_expense.php">
        <label>Category:</label>
        <input type="text" name="category" required>
        <br>
        <label>Amount: ₹</label>
        <input type="number" step="0.01" name="amount" required>
        <br>
        <label>Date:</label>
        <input type="date" name="expense_date" required>
        <br>
        <button type="submit">Add Expense</button>
    </form>
</body>
</html>
