<?php
$title = '部門連絡帳';

// http://qiita.com/naga3/items/0c64981b79c54ebb736f
$db = new PDO('sqlite:todo.db');
$db->exec('CREATE TABLE IF NOT EXISTS todo(id INTEGER PRIMARY KEY, title TEXT, completed INTEGER, edit_date TEXT)');
if (!empty($_GET['action'])) {
  switch ($_GET['action']) {
  case 'list':
    echo json_encode($db->query('SELECT * FROM todo')->fetchAll(PDO::FETCH_ASSOC));
    exit;
  case 'post':
    $db->prepare("INSERT INTO todo(title,completed,edit_date) VALUES(?,0,(select datetime('now' ,'+09:00:00')))")->execute([$_GET['title']]);
    exit;
  case 'delete':
    $db->prepare('DELETE FROM todo WHERE id=?')->execute([$_GET['id']]);
    exit;
  case 'change':
    $db->prepare('UPDATE todo SET completed=? WHERE id=?')->execute([$_GET['completed'], $_GET['id']]);
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="ja" ng-app="app">
<head>
<meta charset="UTF-8">
<title><?=$title;?></title>
<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.1/angular.min.js"></script>
<script>
angular.module('app', []).controller('Controller', function($scope, $http) {
  function api(action, params) {
    params = params || {};
    params.action = action;
    return $http.get('', {params: params});
  }
  function init() {
    $scope.title = '';
    api('list').success(function(res) {
      $scope.todos = res;
    });
  }
  init();
  $scope.post = function() {
    api('post', {title: $scope.title}).success(function() {
      init();
    });
  };
  $scope.delete = function(id) {
    if (confirm("このメッセージを削除しますか？"))
    api('delete', {id: id}).success(function() {
      init();
    });
  };
  $scope.change = function(todo) {
    api('change', {id: todo.id, completed: todo.completed});
  };

  $scope.parseDate = function(d) {
    // ハイフンをスラッシュに全置換
    rep = d.replace( /-/g, "/" );
    date = Date.parse(rep);
    return new Date(date);
  };
});
</script>
</head>

<body ng-controller="Controller">
<p ng-repeat="todo in todos">
  <input type="checkbox" ng-model="todo.completed" ng-true-value="'1'" ng-false-value="'0'" ng-click="change(todo)">
  <span ng-style="{textDecoration: todo.completed === '1' ? 'line-through' : 'none'}">{{todo.title}}</span>
  <span>({{parseDate(todo.edit_date) | date:'MM/dd HH:mm'}})</span>
  <span style="cursor: pointer" ng-click="delete(todo.id)">✕</span>
</p>
<form ng-submit="post()">
  <input ng-model="title">
  <button>Post</button>
</form>
</body>
</html>
